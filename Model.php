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

class Model extends Db {
    
    /**
     * 构造方法，获取表名
     */
    public function __construct()
    {
        parent::__construct();
        $this->getTableName();
    }
    
    /**
     * 获取表名
     * @throws KernelException
     */
    protected function getTableName()
    {
        $prefix = C('database.dbprefix');
        $callClassName = get_called_class();
        $callClassName = explode('\\', $callClassName);
        $callClassName = array_pop($callClassName);
        if (!empty($this->tableName)) {
            if (empty($prefix)) {
                return;
            }
            $this->tableName = $prefix . '_' . $this->tableName;
            return;
        }
        preg_match_all('/([A-Z][a-z][a-zA-Z0-9]*)/', $callClassName, $match);
        if (! isset($match[1][0]) || empty($match[1][0])) {
            throw new KernelException('模型名称无效！');
        }
        $match = $match[1];
        $count = count($match);
        if ($count === 1) {
            $this->tableName = strtolower($match[0]);
            if (empty($prefix)) {
                return;
            }
            $this->tableName = $prefix . '_' . $this->tableName;
            return;
        }
        $last = strtolower(array_pop($match));
        foreach ($match as $v) {
            $this->tableName .= strtolower($v) . '_';
        }
        $this->tableName .= $last;
        if (empty($prefix)) {
            return;
        }
        $this->tableName = $prefix . '_' . $this->tableName;
    }
}