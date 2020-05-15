<?php

namespace SlaveMarket\Lease;

use SlaveMarket\Lease\Factory\ContractFactory;
use SlaveMarket\Lease\Factory\leaseRequestDtoFactory;
use SlaveMarket\Lease\Validator\ContractValidatorsHandler;

/**
 * Операция "Арендовать раба"
 *
 * @package SlaveMarket\Lease
 */
class LeaseOperation {
    /**
     * @var ContractValidatorsHandler
     */
    private $validatorsHandler;
    /**
     * @var leaseRequestDtoFactory
     */
    private $leaseRequestDtoFactory;
    /**
     * @var ContractFactory
     */
    private $contractFactory;

    /**
     * LeaseOperation constructor.
     * @param leaseRequestDtoFactory $leaseRequestDtoFactory
     * @param ContractValidatorsHandler $validatorsHandler
     * @param ContractFactory $contractFactory
     */
    public function __construct(ContractValidatorsHandler $validatorsHandler, leaseRequestDtoFactory $leaseRequestDtoFactory, ContractFactory $contractFactory) {
        $this->validatorsHandler = $validatorsHandler;
        $this->leaseRequestDtoFactory = $leaseRequestDtoFactory;
        $this->contractFactory = $contractFactory;
    }

    /**
     * Выполнить операцию
     *
     * @param LeaseRequest $request
     * @return LeaseResponse
     */
    public function run(LeaseRequest $request): LeaseResponse {
        $requestDto = $this->leaseRequestDtoFactory->createFromLeaseRequest($request);
        $this->validatorsHandler->validate($requestDto);

        $leaseResponse = new LeaseResponse();

        if($this->validatorsHandler->isValid()) {
            $leaseResponse->setLeaseContract($this->contractFactory->createFromDto($requestDto->contractDto));
        } else {
            foreach($this->validatorsHandler->getErrors() as $error) {
                $leaseResponse->addError($error);
            }
        }

        return $leaseResponse;
    }
}