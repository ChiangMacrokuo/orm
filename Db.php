<?php
/**********************************************************************
 * JUST LOVE EIPHP
***********************************************************************
* Copyright (c) 2017 http://www.eiphp.com All rights reserved.
***********************************************************************
* Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
***********************************************************************
* Author: ChiangMacrokuo <420921698@qq.com>
***********************************************************************/
namespace Kernel\Orm;
use Kernel\Application\Application;
class Db
{
    //引用Interpreter
    use Interpreter;
    
    /**
     * 数据库类型，目前只支持mysql
     * @var string
     */
    protected $dbType = '';
    
    /**
     * 表名称
     * @var string
     */
    protected $tableName = '';
    
    /**
     * 数据库策略映射，目前只支持mysql
     * @var array
     */
    protected $dbStrategyMap = [
        'mysql' => 'Kernel\Orm\Db\Mysql'  
    ];
    
    /**
     * DB单例对象
     * @var object
     */
    protected $dbInstance = NULL;
    
    /**
     * 自增id，插入数据成功后的自增id，0为插入失败
     * @var string
     */
    public $id = '';
    
    /**
     * 构造函数
     */
    public function __construct()
    {
        $this->init();
    }
    
    /**
     * 初始化策略
     * @return void
     */
    protected function init()
    {
        $this->dbType = C('database.dbtype');
        $this->decide();
    }
    
    /**
     * 策略决策
     * @return void
     */
    protected function decide()
    {
        $dbStrategyName = $this->dbStrategyMap[$this->dbType];
        $this->dbInstance = Application::$container->get($this->dbType,function()use($dbStrategyName){return new $dbStrategyName();});
    }
    
    /**
     * 设置表名
     * @param string $tableName 表名称
     * @return void
     */
    public function table($tableName = '')
    {
        $dbObject =  new self;
        $dbObject->tableName = $tableName;
        $prefix = C('database.dbprefix');
        if (!$prefix){
            $dbObject->tableName = $prefix . $tableName;
        }
        $dbObject->init();
        return $dbObject;
    }
    
    /**
     * 查找一条数据
     * @param  array $data 查询的字段
     * @return array
     */
    public function one(Array $data = [])
    {
        $this->select($data);
        $this->buildSql();
        $functionName = __FUNCTION__;
        return $this->dbInstance->$functionName($this);
    }
    
    /**
     * 查找所有数据
     * @param  array $data 查询的字段
     * @return array
     */
    public function all(Array $data = [])
    {
        $this->select($data);
        $this->buildSql();
        $functionName = __FUNCTION__;
        return $this->dbInstance->$functionName($this);
    }
    
    /**
     * 保存数据
     * @param array $data 保存的数据
     * @return mixed
     */
    public function save(Array $data = [])
    {
        $this->insert($data);
        $functionName = __FUNCTION__;
        return $this->dbInstance->$functionName($this);
    }
    
    /**
     * 删除数据
     * @return mixed
     */
    public function del()
    {
        $this->delete();
        $this->buildSql();
        $functionName = __FUNCTION__;
        return $this->dbInstance->$functionName($this);
    }
    
    /**
     * 更新数据
     * @param array $data
     */
    public function modify(Array $data = [])
    {
        $this->update($data);
        $this->buildSql();
        $functionName = __FUNCTION__;
        return $this->dbInstance->$functionName($this);
    }
    
    /**
     * 统计记录数量
     * @param  string $data 字段
     * @return number
     */
    public function count($data)
    {
        $this->countColumn($data);
        $this->buildSql();
        return $this->dbInstance->all($this);
    }
    
    /**
     * 数据求和
     * @param  string $data 数据
     * @return number
     */
    public function sum($data)
    {
        $this->sumColumn($data);
        $this->buildSql();
        return $this->dbInstance->all($this);
    }
    
    public function query($sql)
    {
        $this->querySql($sql);
        return $this->dbInstance->query($this);
    }
    
    public function execute($sql)
    {
        $this->querySql($sql);
        return $this->dbInstance->execute($this);
    }
    
    /**
     * 构建sql语句
     * @return void
     */
    protected function buildSql()
    {
        if (!empty($this->where)){
            $this->sql .= $this->where;
        }
        if (!empty($this->orderBy)){
            $this->sql .= $this->orderBy;
        }
        if (!empty($this->limit)){
            $this->sql .= $this->limit;
        }
    }
    
    /**
     * 开启事物
     */
    public function beginTransaction()
    {
        $this->dbInstance->beginTransaction();
    }
    
    /**
     * 提交事物
     */
    public function commit()
    {
        $this->dbInstance->commit();
    }
    
    /**
     * 完成事物
     */
    public function rollBack()
    {
        $this->dbInstance->rollBack();
    }
}