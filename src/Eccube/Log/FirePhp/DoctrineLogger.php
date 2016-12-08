<?php

/*
 * This file is part of EC-CUBE
 *
 * Copyright(c) 2000-2015 LOCKON CO.,LTD. All Rights Reserved.
 *
 * http://www.lockon.co.jp/
 *
 * This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License
 * as published by the Free Software Foundation; either version 2
 * of the License, or (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program; if not, write to the Free Software
 * Foundation, Inc., 59 Temple Place - Suite 330, Boston, MA  02111-1307, USA.
 */

namespace Eccube\Log\FirePhp;

use Monolog\Logger;
use Monolog\Handler\FirePHPHandler;
use Monolog\Formatter\LogglyFormatter;

class DoctrineLogger implements \Doctrine\DBAL\Logging\SQLLogger
{

    private $cnt = 0;

    public function __construct()
    {
        $this->logger = new Logger('SQL');
        $this->logger->pushHandler(new FirePHPHandler(Logger::INFO));
    }

    private $app = null;

    public function getApp()
    {
        return $this->app;
    }

    public function setApp($app)
    {
        $this->app = $app;
    }

    public $logger = null;
    
    private $microtime = null;

    /**
     * {@inheritdoc}
     */
    public function startQuery($sql, array $params = null, array $types = null)
    {
        if (is_array($params)) {
            foreach ($params as $keys => $param) {
                $sql = $this->replace($sql, $param);
            }
        }
        $this->cnt++;
        $this->logger->addInfo('(' . $this->cnt . ') ' . $sql);
        $this->microtime = microtime(true);
    }

    /**
     * {@inheritdoc}
     */
    public function stopQuery()
    {
        $this->logger->addInfo('time='.round((microtime(true) - $this->microtime),4));
    }

    private function replace($sql, $param)
    {
        if (is_array($param)) {
            $param = "'" . join("','", $param) . "'";
        } elseif($param instanceof \DateTime) {
            $param = "'" . $param->format('Y-m-d H:i:s') . "'";
        } else {
            $param = "'" . $param . "'";
        }
        $needle = '?';
        $pos = strpos($sql, $needle);
        if ($pos !== false) {
            $sql = substr_replace($sql, $param, $pos, 1);
        }
        return $sql;
    }
}
