<?php

namespace SlaveMarket\Lease\Dto;

use SlaveMarket\Lease\LeaseRequest;

class LeaseRequestDto {
    /**
     * @var LeaseRequest
     */
    public $leaseRequest;
    /**
     * @var ContractDto
     */
    public $contractDto;

    /**
     * @var ContractDto[]
     */
    public $previousContractsDto;
}