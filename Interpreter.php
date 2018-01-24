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
use Kernel\Exception\KernelException;

trait Interpreter {
    
    /**
     * 查询条件
     * @var string
     */
    private $where = '';
    
    /**
     * 排序条件
     * @var string
     */
    private $orderBy = '';
    
    /**
     * 限制条件
     * @var string
     */
    private $limit = '';
    
    /**
     * 查询偏移量
     * @var string
     */
    private $offset ='';
    
    /**
     * 查询SQL语句
     * @var string
     */
    public $sql = '';
    
    /**
     * 查询参数
     * @var string
     */
    public $parameter = '';
    
    /**
    *  插入一条数据
    * @param  array $data 数据
    * @return void
    */
    public function insert(Array $data = [])
    {
        if (empty($data)){
            throw new KernelException('参数data为空！');
        }                
        $fieldString = $valueString = '';
        $dataCopy = $data;
        $pop = array_pop($dataCopy);
        foreach ($data as $k => $v) {
            if ($v === $pop) {
                $fieldString .= "`{$k}`";
                $valueString .= ":{$k}";
                $this->parameter[$k] = $v;
                continue;
            }
            $fieldString .= "`{$k}`".',';
            $valueString .= ":{$k},";
            $this->parameter[$k] = $v;
        }
        $this->sql = "INSERT INTO `{$this->tableName}` ({$fieldString}) VALUES ({$valueString})";
    }
    
    /**
     *  删除数据
     * @return void
     */
    public function delete()
    {
        $this->sql = "DELETE FROM `{$this->tableName}`";
    }
    
    /**
     * 更新一条数据
     * @param  array $data 数据
     * @return void
     */
    public function update($data = [])
    {
        if (empty($data)) {
            throw new KernelException("参数data为空！");
        }
        $set = '';
        $dataCopy = $data;
        $pop = array_pop($dataCopy);
        foreach ($data as $k => $v) {
            if ($v === $pop) {
                $set .= "`{$k}` = :$k";
                $this->parameter[$k] = $v;
                continue;
            }
            $set .= "`{$k}` = :$k,";
            $this->parameter[$k] = $v;
        }
        $this->sql = "UPDATE `{$this->tableName}` SET {$set}";
    }
    
    
    /**
     * 查找一条数据
     * @param  array $data 查询的字段
     * @return void
     */
    public function select(Array $data = [])
    {
        if (!is_array($data)){
            throw new KernelException('数据data格式错误！');
        }
        $field = '';
        $count = count($data);
        if ($count === 0){
            $field = '*';
        }else {
            $last = array_pop($data);
            foreach ($data as $v) {
                $field .= "`{$v}`,";
            }
            $field .= "`{$last}`";
        }
        $this->sql = "SELECT $field FROM `{$this->tableName}`";
    }
    
    /**
     * where 条件
     * @param  array $data 数据
     * @return void
     */
    public function where(Array $data = [])
    {
        if (empty($data)) {
            return $this;
        }
        $count = count($data);
        if ($count === 1) {
            $field = array_keys($data)[0];
            $value = array_values($data)[0];
            if (!is_array($value)){
                $this->where  = " WHERE `{$field}` = :{$field}";
                $this->parameter = $data;
                return $this;
            }
            if (strtoupper($value[0]) === "IN"){
                $where = "";
                $this->where = " WHERE `{$field}` IN (";
                foreach ($value[1] as $key => $val){
                    $where .= ' :' . $field . '_' . $key . ',';
                    $this->parameter[$field . '_' . $key] = $val;
                }

                $this->where .= rtrim($where,',') . ')';
                unset($key);
                unset($val);
                unset($where);
                return $this;
            }
            $this->where = " WHERE `{$field}` {$value[0]} :{$field}";
            $this->parameter[$field] = $value[1];
            return $this;
        }
        $tmp  = $data;
        $last = array_pop($tmp);
        foreach ($data as $key => $val) {
            if ($val === $last) {
                if (!is_array($val)){
                    $this->where .= "`{$key}` = :{$key}";
                    $this->parameter[$key] = $val;
                    continue;
                }
                if (strtoupper($val[0]) === "IN"){
                    $where = "";
                    $this->where = " `{$key}` IN (";
                    foreach ($val[1] as $k => $v){
                        $where .= ' :' . $key . '_' . $k . ',';
                        $this->parameter[$key . '_' . $k] = $v;
                    }
                    $this->where .= rtrim($where,',') . ')';
                    unset($k);
                    unset($v);
                    unset($where);
                    continue;
                }
                $this->where .= "`{$key}` {$val[0]} :{$key}";
                $this->parameter[$key] = $val[1];
                continue;
            }
            if (!is_array($val)){
                $this->where  .= " WHERE `{$key}` = :{$key} AND ";
                $this->parameter[$key] = $val;
                continue;
            }
            if (strtoupper($val[0]) === "IN"){
                $where = "";
                $this->where = " WHERE `{$key}` IN (";
                foreach ($val[1] as $k => $v){
                    $where .= ' :' . $key . '_' . $k . ',';
                    $this->parameter[$key . '_' . $k] = $v;
                }
                $this->where .= rtrim($where,',') . ')';
                unset($k);
                unset($v);
                unset($where);
                continue;
            }
            $this->where .= " WHERE `{$key}` {$val[0]} :{$key} AND ";
            $this->parameter[$key] = $val[1];
            continue;
        }
        return $this;
    }
    
    
    
    /**
     * 排序
     * @param string $sort 排序参数，（例如；id desc）
     * @throws KernelException
     * @return \Kernel\Orm\Interpreter
     */
    public function orderBy($sort = 'id desc')
    {
        if (!is_string($sort)){
            throw new KernelException('参数sort不是字符串类型！');
        }
        $this->orderBy = " ORDER BY {$sort}";
        return $this;
    }
    
    /**
     * 限制
     * @param number $start 查询开始位置
     * @param number $len 查询数量
     * @throws KernelException
     * @return \Kernel\Orm\Interpreter
     */
    public function limit($start = 0, $len = 0)
    {
        if (!is_numeric($start) || !is_numeric($len)){
            throw new KernelException('参数类型错误，非数字型！');
        }
        if ($len ==0){
            $this->limit = " LIMIT {$start}";
            return $this;
        }
        $this->limit = " LIMIT {$start},{$len}";
        return $this;
    }
    
    /**
     * count column
     *
     * @param  string $data 查询的字段
     * @return mixed
     */
    public function countColumn($data)
    {
        $field     = $this->packColumn('count',$data);
        $this->sql = "SELECT $field FROM `{$this->tableName}`";
    }

    /**
     * sum column
     *
     * @param  string $data 查询的字段
     * @return mixed
     */
    public function sumColumn($data)
    {
        $field     = $this->packColumn('sum',$data);
        $this->sql = "SELECT $field FROM `{$this->tableName}`";
    }

    /**
     * 组装mysql函数字段
     *
     * @param  string $functionName mysql函数名称
     * @param  string $data         参数
     * @return string
     */
    public function packColumn($functionName = '', $data = '')
    {
        $field     = "{$functionName}(`{$data}`)";
        preg_match_all('/(\w+)\sas/', $data, $matchColumn);
        if (isset($matchColumn[1][0]) || (! empty($matchColumn[1][0]))) {
            $matchColumn = $matchColumn[1][0];
            $field = "{$functionName}(`{$matchColumn}`)";
            preg_match_all('/as\s(\w+)/', $data, $match);
            if (isset($match[1][0]) || (! empty($match[1][0]))) {
                $match = $match[1][0];
                $field .= " as `{$match}`";
            }
        }
        return $field;
    }
    
    
    /**
     * query
     * @param string $sql 查询
     * @throws KernelException
     */
    public function querySql($sql = '')
    {
        if (empty($sql)){
            throw new KernelException('SQL语句为空！');
        }
        $this->sql = $sql;
    }
}

