<?php


namespace SlaveMarket\Lease\Dto;

use SlaveMarket\Master;
use SlaveMarket\Slave;

class ContractDto {
    /**
     * @var int
     */
    public $id;
    /**
     * @var Slave
     */
    public $slave;
    /**
     * @var Master
     */
    public $master;
    /**
     * @var ContractDayDto[]
     */
    public $days;
}