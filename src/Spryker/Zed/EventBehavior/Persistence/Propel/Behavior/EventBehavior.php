<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace Spryker\Zed\EventBehavior\Persistence\Propel\Behavior;

use Propel\Generator\Model\Behavior;
use Propel\Generator\Util\PhpParser;
use Propel\Runtime\Exception\PropelException;
use Zend\Filter\Word\UnderscoreToCamelCase;

class EventBehavior extends Behavior
{
    public const EVENT_CHANGE_ENTITY_NAME = 'name';
    public const EVENT_CHANGE_ENTITY_ID = 'id';
    public const EVENT_CHANGE_ENTITY_FOREIGN_KEYS = 'foreignKeys';
    public const EVENT_CHANGE_ENTITY_MODIFIED_COLUMNS = 'modifiedColumns';
    public const EVENT_CHANGE_ENTITY_ORIGINAL_VALUES = 'originalValues';
    public const EVENT_CHANGE_ENTITY_ADDITIONAL_VALUES = 'additionalValues';
    public const EVENT_CHANGE_NAME = 'event';

    /**
     * @return string
     */
    public function preSave()
    {
        return "
\$this->prepareSaveEventName();
        ";
    }

    /**
     * @return string
     */
    public function postSave()
    {
        return "
if (\$affectedRows) {
    \$this->addSaveEventToMemory();
}
        ";
    }

    /**
     * @return string
     */
    public function postDelete()
    {
        return "
\$this->addDeleteEventToMemory();
        ";
    }

    /**
     * Adds a single parameter.
     *
     * Expects an associative array looking like
     * [ 'name' => 'foo', 'value' => bar ]
     *
     * @param array $parameter
     *
     * @throws \Propel\Runtime\Exception\PropelException
     *
     * @return void
     */
    public function addParameter(array $parameter)
    {
        $parameter = array_change_key_case($parameter, CASE_LOWER);

        $this->parameters[$parameter['name']] = [];

        if (!isset($parameter['column'])) {
            throw new PropelException(sprintf('"column" attribute for %s event behavior is missing', $parameter['name']));
        }

        $this->parameters[$parameter['name']]['column'] = $parameter['column'];

        if (isset($parameter['value'])) {
            $this->parameters[$parameter['name']]['value'] = $parameter['value'];
        }

        if (isset($parameter['operator'])) {
            $this->parameters[$parameter['name']]['operator'] = $parameter['operator'];
        }

        if (isset($parameter['keep-original'])) {
            $this->parameters[$parameter['name']]['keep-original'] = $parameter['keep-original'];
        }

        if (isset($parameter['keep-additional'])) {
            $this->parameters[$parameter['name']]['keep-additional'] = $parameter['keep-additional'];
        }
    }

    /**
     * @return string
     */
    public function objectAttributes()
    {
        $script = '';
        $script .= $this->addEventAttributes();
        $script .= $this->addForeignKeysAttribute();

        return $script;
    }

    /**
     * @param string $script
     *
     * @return void
     */
    public function objectFilter(&$script)
    {
        $parser = new PhpParser($script, true);
        $eventColumns = $this->getParameters();

        foreach ($eventColumns as $eventColumn) {
            if ($eventColumn['column'] === '*') {
                continue;
            }
            $this->addSetInitialValueStatement($parser, $eventColumn['column']);
        }

        $script = $parser->getCode();
    }

    /**
     * @return string
     */
    public function objectMethods()
    {
        $script = '';
        $script .= $this->addPrepareEventMethod();
        $script .= $this->addToggleEventMethod();
        $script .= $this->addSaveEventMethod();
        $script .= $this->addDeleteEventMethod();
        $script .= $this->addGetForeignKeysMethod();
        $script .= $this->addSaveEventBehaviorEntityChangeMethod();
        $script .= $this->addIsEventColumnsModifiedMethod();
        $script .= $this->addGetOriginalValuesMethod();
        $script .= $this->addGetAdditionalValuesMethod();
        $script .= $this->addGetPhpType();

        return $script;
    }

    /**
     * @param \Propel\Generator\Util\PhpParser $parser
     * @param string $column
     *
     * @return void
     */
    protected function addSetInitialValueStatement(PhpParser $parser, string $column)
    {
        $camelCaseFilter = new UnderscoreToCamelCase();

        $methodName = sprintf('set%s', $camelCaseFilter->filter($column));
        $initialValueField = sprintf("[%sTableMap::COL_%s]", $this->getTable()->getPhpName(), strtoupper($column));

        $methodNamePattern = '(' . $methodName . '\(\$v\)\n[ ]*{)';
        $newMethodCode = preg_replace_callback($methodNamePattern, function ($matches) use ($initialValueField, $column) {
            return $matches[0] . "\n\t\t\$this->_initialValues$initialValueField = \$this->$column;\n";
        }, $parser->findMethod($methodName));

        $parser->replaceMethod($methodName, $newMethodCode);
    }

    /**
     * @return string
     */
    protected function addEventAttributes()
    {
        return "
/**
 * @var string
 */
private \$_eventName;

/**
 * @var bool
 */
private \$_isModified;

/**
 * @var array
 */
private \$_modifiedColumns;

/**
 * @var array
 */
private \$_initialValues;
        
/**
 * @var bool
 */
private \$_isEventDisabled;        
        ";
    }

    /**
     * @return string
     */
    protected function addForeignKeysAttribute()
    {
        $foreignKeys = $this->getTable()->getForeignKeys();
        $tableName = $this->getTable()->getName();
        $implodedForeignKeys = '';

        foreach ($foreignKeys as $foreignKey) {
            $fullColumnName = sprintf("%s.%s", $tableName, $foreignKey->getLocalColumnName());
            $implodedForeignKeys .= sprintf("
    '%s' => '%s',", $fullColumnName, $foreignKey->getLocalColumnName());
        }

        return "
/**
 * @var array
 */
private \$_foreignKeys = [$implodedForeignKeys
];        
        ";
    }

    /**
     * @return string
     */
    protected function addPrepareEventMethod()
    {
        $createEvent = 'Entity.' . $this->getTable()->getName() . '.create';
        $updateEvent = 'Entity.' . $this->getTable()->getName() . '.update';

        return "
/**
 * @return void
 */
protected function prepareSaveEventName()
{
    if (\$this->isNew()) {
        \$this->_eventName = '$createEvent';
    } else {
        \$this->_eventName = '$updateEvent';
    }

    \$this->_modifiedColumns = \$this->getModifiedColumns();
    \$this->_isModified = \$this->isModified();
}
        ";
    }

    /**
     * @return string
     */
    protected function addToggleEventMethod()
    {
        return "
/**
 * @return void
 */
public function disableEvent()
{
    \$this->_isEventDisabled = true;
}

/**
 * @return void
 */
public function enableEvent()
{
    \$this->_isEventDisabled = false;
}        
        ";
    }

    /**
     * @return string
     */
    protected function addSaveEventMethod()
    {
        $tableName = $this->getTable()->getName();
        $dataEventEntityName = static::EVENT_CHANGE_ENTITY_NAME;
        $dataEventEntityId = static::EVENT_CHANGE_ENTITY_ID;
        $dataEventEntityForeignKeys = static::EVENT_CHANGE_ENTITY_FOREIGN_KEYS;
        $dataEventEntityModifiedColumns = static::EVENT_CHANGE_ENTITY_MODIFIED_COLUMNS;
        $dataEventEntityOriginalValues = static::EVENT_CHANGE_ENTITY_ORIGINAL_VALUES;
        $dataEventEntityAdditionalValues = static::EVENT_CHANGE_ENTITY_ADDITIONAL_VALUES;
        $dataEventName = static::EVENT_CHANGE_NAME;

        return "
/**
 * @return void
 */
protected function addSaveEventToMemory()
{
    if (\$this->_isEventDisabled) {
        return;
    }
    
    if (\$this->_eventName !== 'Entity.$tableName.create') {       
        if (!\$this->_isModified) {
            return;
        }
        
        if (!\$this->isEventColumnsModified()) {
            return;
        }
    }
    
    \$data = [
        '$dataEventEntityName' => '$tableName',
        '$dataEventEntityId' => \$this->getPrimaryKey(),
        '$dataEventName' => \$this->_eventName,
        '$dataEventEntityForeignKeys' => \$this->getForeignKeys(),
        '$dataEventEntityModifiedColumns' => \$this->_modifiedColumns,
        '$dataEventEntityOriginalValues' => \$this->getOriginalValues(),
        '$dataEventEntityAdditionalValues' => \$this->getAdditionalValues(),
    ];

    \$this->saveEventBehaviorEntityChange(\$data);

    unset(\$this->_eventName);
    unset(\$this->_modifiedColumns);
    unset(\$this->_isModified);
}
        ";
    }

    /**
     * @return string
     */
    protected function addDeleteEventMethod()
    {
        $tableName = $this->getTable()->getName();
        $deleteEvent = 'Entity.' . $tableName . '.delete';
        $dataEventEntityName = static::EVENT_CHANGE_ENTITY_NAME;
        $dataEventEntityId = static::EVENT_CHANGE_ENTITY_ID;
        $dataEventEntityForeignKeys = static::EVENT_CHANGE_ENTITY_FOREIGN_KEYS;
        $dataEventName = static::EVENT_CHANGE_NAME;
        $dataEventEntityAdditionalValues = static::EVENT_CHANGE_ENTITY_ADDITIONAL_VALUES;

        return "
/**
 * @return void
 */
protected function addDeleteEventToMemory()
{
    if (\$this->_isEventDisabled) {
        return;
    }

    \$data = [
        '$dataEventEntityName' => '$tableName',
        '$dataEventEntityId' => \$this->getPrimaryKey(),
        '$dataEventName' => '$deleteEvent',
        '$dataEventEntityForeignKeys' => \$this->getForeignKeys(),
        '$dataEventEntityAdditionalValues' => \$this->getAdditionalValues(),
    ];

    \$this->saveEventBehaviorEntityChange(\$data);
}
        ";
    }

    /**
     * @return string
     */
    protected function addGetForeignKeysMethod()
    {
        return "
/**
 * @return array
 */        
protected function getForeignKeys()
{
    \$foreignKeysWithValue = [];
    foreach (\$this->_foreignKeys as \$key => \$value) {
        \$foreignKeysWithValue[\$key] = \$this->getByName(\$value);
    }
    
    return \$foreignKeysWithValue;
}
        ";
    }

    /**
     * @return string
     */
    protected function addSaveEventBehaviorEntityChangeMethod()
    {
        return "
/**
 * @param array \$data
 *
 * @return void
 */
protected function saveEventBehaviorEntityChange(array \$data)
{
    \$spyEventBehaviorEntityChange = new \\Orm\\Zed\\EventBehavior\\Persistence\\SpyEventBehaviorEntityChange();
    \$spyEventBehaviorEntityChange->setData(json_encode(\$data));
    \$spyEventBehaviorEntityChange->setProcessId(\\Spryker\\Zed\\Kernel\\RequestIdentifier::getRequestId());
    \$spyEventBehaviorEntityChange->save();
}        
        ";
    }

    /**
     * @return string
     */
    protected function addIsEventColumnsModifiedMethod()
    {
        $eventParameters = $this->getParameters();
        $tableName = $this->getTable()->getName();
        $implodedModifiedColumns = '';

        foreach ($eventParameters as $eventParameter) {
            if ($eventParameter['column'] === '*') {
                return "
/**
 * @return bool
 */
protected function isEventColumnsModified()
{            
    /* There is a wildcard(*) property for this event */
    return true;
}
            ";
            }
        }

        foreach ($this->getParameters() as $columnAttribute) {
            $implodedAttributes = '';
            foreach ($columnAttribute as $key => $value) {
                $implodedAttributes .= sprintf("
                '$key' => '$value',");
            }

            $implodedModifiedColumns .= sprintf("
            '%s.%s' => [$implodedAttributes
            ],", $tableName, $columnAttribute['column']);
        }

        return "
/**
 * @return bool
 */
protected function isEventColumnsModified()
{
    \$eventColumns = [$implodedModifiedColumns
    ];
    
    foreach (\$this->_modifiedColumns as \$modifiedColumn) {
        if (isset(\$eventColumns[\$modifiedColumn])) {           
            
            if (!isset(\$eventColumns[\$modifiedColumn]['value'])) {
                return true;
            }
            
            \$xmlValue = \$eventColumns[\$modifiedColumn]['value'];
            \$xmlValue = \$this->getPhpType(\$xmlValue, \$modifiedColumn);
            \$xmlOperator = '';
            if (isset(\$eventColumns[\$modifiedColumn]['operator'])) {
                \$xmlOperator = \$eventColumns[\$modifiedColumn]['operator'];
            }
            \$before = \$this->_initialValues[\$modifiedColumn];
            \$field = str_replace('$tableName.', '', \$modifiedColumn);
            \$after = \$this->\$field;
            
            if (\$before === null && \$after !== null) {
                return true;
            }

            if (\$before !== null && \$after === null) {
                return true;
            }

            switch (\$xmlOperator) {
                case '<':
                    \$result = (\$before < \$xmlValue xor \$after < \$xmlValue);
                    break;
                case '>':
                    \$result = (\$before > \$xmlValue xor \$after > \$xmlValue);
                    break;
                case '<=':
                    \$result = (\$before <= \$xmlValue xor \$after <= \$xmlValue);
                    break;
                case '>=':
                    \$result = (\$before >= \$xmlValue xor \$after >= \$xmlValue);
                    break;
                case '<>':
                    \$result = (\$before <> \$xmlValue xor \$after <> \$xmlValue);
                    break;
                case '!=':
                    \$result = (\$before != \$xmlValue xor \$after != \$xmlValue);
                    break;
                case '==':
                    \$result = (\$before == \$xmlValue xor \$after == \$xmlValue);
                    break;
                case '!==':
                    \$result = (\$before !== \$xmlValue xor \$after !== \$xmlValue);
                    break;     
                default:
                    \$result = (\$before === \$xmlValue xor \$after === \$xmlValue);
            }
            
            if (\$result) {
                return true;
            }
        }
    }

    return false;
}        
        ";
    }

    /**
     * @return string
     */
    protected function addGetAdditionalValuesMethod()
    {
        $tableName = $this->getTable()->getName();
        $additionalColumns = $this->getAdditionalColumnNames();
        $implodedAdditionalColumnNames = implode("\n", array_map(function ($columnName) {
            return sprintf("\t'%s',", $columnName);
        }, $additionalColumns));

        return "
/**
 * @return array
 */
protected function getAdditionalValueColumnNames(): array
{
    return [
        $implodedAdditionalColumnNames
    ];
}

/**
 * @return array
 */
protected function getAdditionalValues(): array
{
    \$additionalValues = [];
    foreach (\$this->getAdditionalValueColumnNames() as \$additionalValueColumnName) {          
        \$field = str_replace('$tableName.', '', \$additionalValueColumnName);  
        \$additionalValues[\$additionalValueColumnName] = \$this->\$field;
    }

    return \$additionalValues;
}        
        ";
    }

    /**
     * @return string
     */
    protected function addGetOriginalValuesMethod()
    {
        $tableName = $this->getTable()->getName();
        $originalValueColumns = $this->getKeepOriginalValueColumnNames();
        $implodedOriginalValueColumnNames = implode("\n", array_map(function ($columnName) {
            return sprintf("\t'%s',", $columnName);
        }, $originalValueColumns));

        return "
/**
 * @return array
 */
protected function getOriginalValueColumnNames(): array
{
    return [
    $implodedOriginalValueColumnNames
    ];
}

/**
 * @return array
 */
protected function getOriginalValues(): array
{
    if (\$this->isNew()) {
        return [];
    }

    \$originalValues = [];
    foreach (\$this->_modifiedColumns as \$modifiedColumn) {            
        if (!in_array(\$modifiedColumn, \$this->getOriginalValueColumnNames())) {
            continue;
        }

        \$before = \$this->_initialValues[\$modifiedColumn];
        \$field = str_replace('$tableName.', '', \$modifiedColumn);
        \$after = \$this->\$field;
        
        if (\$before !== \$after) {
            \$originalValues[\$modifiedColumn] = \$before;
        }
    }

    return \$originalValues;
}        
        ";
    }

    /**
     * @return array
     */
    protected function getAdditionalColumnNames(): array
    {
        $additionalColumns = [];
        $tableName = $this->getTable()->getName();
        $eventColumns = $this->getParameters();
        foreach ($eventColumns as $eventColumn) {
            if ($eventColumn['column'] === '*' && isset($eventColumn['keep-additional']) && $eventColumn['keep-additional'] === 'true') {
                return $this->getTableFullColumnNames();
            }

            if (isset($eventColumn['keep-additional']) && $eventColumn['keep-additional'] === 'true') {
                $additionalColumns[] = $this->formatFullColumnName($tableName, $eventColumn['column']);
            }
        }

        return $additionalColumns;
    }

    /**
     * @return array
     */
    protected function getKeepOriginalValueColumnNames(): array
    {
        $originalValueColumns = [];
        $tableName = $this->getTable()->getName();
        $eventColumns = $this->getParameters();
        foreach ($eventColumns as $eventColumn) {
            if ($eventColumn['column'] === '*' && isset($eventColumn['keep-original']) && $eventColumn['keep-original'] === 'true') {
                return $this->getTableFullColumnNames();
            }

            if (isset($eventColumn['keep-original']) && $eventColumn['keep-original'] === 'true') {
                $originalValueColumns[] = $this->formatFullColumnName($tableName, $eventColumn['column']);
            }
        }

        return $originalValueColumns;
    }

    /**
     * @return array
     */
    protected function getTableFullColumnNames(): array
    {
        $tableName = $this->getTable()->getName();

        return array_reduce($this->getTable()->getColumns(), function ($columns, $columnObj) use ($tableName) {
            $columns[] = $this->formatFullColumnName($tableName, $columnObj->getName());

            return $columns;
        }, []);
    }

    /**
     * @param string $tableName
     * @param string $columnName
     *
     * @return string
     */
    protected function formatFullColumnName(string $tableName, string $columnName): string
    {
        return sprintf('%s.%s', $tableName, $columnName);
    }

    /**
     * @return string
     */
    public function addGetPhpType()
    {
        $tableMapPhpName = sprintf('%s%s', $this->getTable()->getPhpName(), 'TableMap');

        return "
/**
 * @param string \$xmlValue
 * @param string \$column
 *
 * @return array|bool|\\DateTime|float|int|object
 */
protected function getPhpType(\$xmlValue, \$column)
{
    \$columnType = $tableMapPhpName::getTableMap()->getColumn(\$column)->getType();
    if (in_array(strtoupper(\$columnType), ['INTEGER', 'TINYINT', 'SMALLINT'])) {
        \$xmlValue = (int) \$xmlValue;
    } else if (in_array(strtoupper(\$columnType), ['REAL', 'FLOAT', 'DOUBLE', 'BINARY', 'VARBINARY', 'LONGVARBINARY'])) {
        \$xmlValue = (double) \$xmlValue;
    } else if (strtoupper(\$columnType) === 'ARRAY') {
        \$xmlValue = (array) \$xmlValue;
    } else if (strtoupper(\$columnType) === 'BOOLEAN') {
        \$xmlValue = filter_var(\$xmlValue,  FILTER_VALIDATE_BOOLEAN);
    } else if (strtoupper(\$columnType) === 'OBJECT') {
        \$xmlValue = (object) \$xmlValue;
    } else if (in_array(strtoupper(\$columnType), ['DATE', 'TIME', 'TIMESTAMP', 'BU_DATE', 'BU_TIMESTAMP'])) {
        \$xmlValue = \\DateTime::createFromFormat('Y-m-d H:i:s', \$xmlValue);
    }
    
    return \$xmlValue;
}
        ";
    }
}
