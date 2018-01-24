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
namespace Kernel\Orm\Db;
use Kernel\Orm\Db;
use Kernel\Exception\KernelException;
class Mysql {

    /**
     * 数据主机地址
     * @var string
     */
    private $dbHost = '';
    
    /**
     * 数据库连接端口
     * @var string
     */
    private $dbPort = '';
    
    /**
     * 数据库名称
     * @var string
     */
    private $dbName = '';
    
    /**
     * 数据库DNS
     * @var string
     */
    private $dsn = '';
    
    /**
     * 数据用户名
     * @var string
     */
    private $dbUserName = '';
    
    /**
     * 数据库密码
     * @var string
     */
    private $dbPassword = '';
    
    /**
     * 数据库字符集
     * @var string
     */
    private $character = '';
    
    /**
     * 数据库连接PDO对象
     * @var resource
     */
    private $pdo = NULL;
    
    /**
     * 记录对象
     * @var object
     */
    private $pdoStatement = '';
    
    /**
     * 构造方法、连接数据库
     */
    public function __construct()
    {
        $dbConfig = C('database');
        $this->dbHost = $dbConfig['dbhost'];
        $this->dbPort = $dbConfig['dbport'];
        $this->dbName = $dbConfig['dbname'];
        $this->dsn = "mysql:host={$this->dbHost};port={$this->dbPort};dbname={$this->dbName};";
        $this->dbUserName = $dbConfig['username'];
        $this->dbPassword = $dbConfig['password'];
        $this->character = $dbConfig['character'];
        $this->connect();
    }
    
    /**
     * 链接数据库
     */
    protected function connect()
    {
        try {
            $this->pdo = new \PDO(
                $this->dsn,
                $this->dbUserName,
                $this->dbPassword,
                array(
                    \PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES {$this->character};", 
                    \PDO::ATTR_EMULATE_PREPARES => false,
                    \PDO::ATTR_DEFAULT_FETCH_MODE => \PDO::FETCH_ASSOC,
                    \PDO::ATTR_ERRMODE => \PDO::ERRMODE_EXCEPTION
                )
            );
        }catch (\PDOException $pe){
            throw new KernelException($pe->getMessage());
        }
    }
    
    /**
     * 查找一条数据
     * @param Db $db Db资源
     * @return array
     */
    public function one(Db $db)
    {
        L($this->buildSql($db),'sql');
        $this->pdoStatement = $this->pdo->prepare($db->sql);
        $this->bindValue($db);
        $this->pdoStatement->execute();
        $this->getPDOError();
        return $this->pdoStatement->fetch(\PDO::FETCH_ASSOC);
    }
    
    /**
     * 查找所有数据
     * @param Db $db Db资源
     * @return array
     */
    public function all(Db $db)
    {
        L($this->buildSql($db),'sql');
        $this->pdoStatement = $this->pdo->prepare($db->sql);
        $this->bindValue($db);
        $this->pdoStatement->execute();
        $this->getPDOError();
        return $this->pdoStatement->fetchAll(\PDO::FETCH_ASSOC);
    }
    
    /**
     * 保存数据
     * @param Db $db Db资源
     * @return boolean|number
     */
    public function save(Db $db)
    {
        L($this->buildSql($db),'sql');
        $this->pdoStatement = $this->pdo->prepare($db->sql);
        $this->bindValue($db);
        $this->pdoStatement->execute();
        $this->getPDOError();
        return $db->id = $this->pdo->lastInsertId();
    }
    
    /**
     * 删除数据
     * @param Db $db Db资源
     * @return number
     */
    public function del(Db $db)
    {
        L($this->buildSql($db),'sql');
        $this->pdoStatement = $this->pdo->prepare($db->sql);
        $this->bindValue($db);
        $this->pdoStatement->execute();
        $this->getPDOError();
        return $this->pdoStatement->rowCount();
    }
    
    /**
     * 更新数据
     * @param Db $db Db资源
     * @return number
     */
    public function modify(Db $db)
    {
        L($this->buildSql($db),'sql');
        $this->pdoStatement = $this->pdo->prepare($db->sql);
        $this->bindValue($db);
        $this->pdoStatement->execute();
        $this->getPDOError();
        return $this->pdoStatement->rowCount();
            
    }
    
    /**
     * 执行SQL语句
     * @param Db $db Db资源
     * @return array
     */
    public function query(Db $db)
    {
        L($db->sql,'sql');
        $res = [];
        $pdoStatement = $this->pdo->query($db->sql);
        $this->getPDOError();
        $pdoStatement->setFetchMode(\PDO::FETCH_ASSOC);
        $res = $pdoStatement->fetchAll();
        return $res;
    }
    
    /**
     * 执行SQL语句
     * @param Db $db Db资源
     * @return mixed
     */
    public function execute(Db $db)
    {
        L($db->sql,'sql');
        $res = $this->pdo->exec($db->sql);
        $this->getPDOError();
        return $res;
    }
    
    /**
     * 绑定数据
     * @param Db $db
     * @return void
     */
    private function bindValue(Db $db)
    {
        if (empty($db->parameter)) {
            return;
        }
        foreach ($db->parameter as $k => $v) {
            $this->pdoStatement->bindValue(":{$k}", $v);
        }   
    }
    
    /**
     * 开启事物
     */
    public function beginTransaction()
    {
        $this->pdo->beginTransaction();
    }
    
    /**
     * 提交事物
     */
    public function commit()
    {
        $this->pdo->commit();
    }
    
    /**
     * 回滚事务
     */
    public function rollBack()
    {
        $this->pdo->rollBack();
    }
    
    /**
     * 获取PDO错误信息
     */
    private function getPDOError()
    {
        if($this->pdo->errorCode() != '00000') {
            $error = $this->pdo->errorInfo();
            throw new KernelException($error[2]);
        }
    }
    
    /**
     * 重新组装SQL
     * @param Db $db
     * @return mixed
     */
    private function buildSql(Db $db)
    {
        $query = $db->sql;
        if (!empty($db->parameter)) {
            foreach ($db->parameter as $key => $value){
                if (!is_string($value)){
                    $query = str_replace(":{$key}", $value, $query);
                }
                $query = str_replace(":{$key}", "'{$value}'", $query);
            }
        }
        return $query;
    }
}