<?php


namespace SlaveMarket\Lease\Factory;

use Exception;
use SlaveMarket\Lease\Dto\ContractDto;
use SlaveMarket\Lease\LeaseContract;
use SlaveMarket\Lease\LeaseRequest;
use SlaveMarket\MastersRepository;
use SlaveMarket\SlavesRepository;

class ContractDtoFactory {

    /**
     * @var MastersRepository
     */
    protected $masterRepository;
    /**
     * @var SlavesRepository
     */
    protected $slavesRepository;
    /**
     * @var ContractDaysDtoFactory
     */
    protected $contractDaysDtoFactory;

    /**
     * ContractDtoFactory constructor.
     * @param ContractDaysDtoFactory $contractDaysDtoFactory
     * @param MastersRepository $masterRepository
     * @param SlavesRepository $slavesRepository
     */
    public function __construct(ContractDaysDtoFactory $contractDaysDtoFactory, MastersRepository $masterRepository, SlavesRepository $slavesRepository) {
        $this->masterRepository = $masterRepository;
        $this->slavesRepository = $slavesRepository;
        $this->contractDaysDtoFactory = $contractDaysDtoFactory;
    }


    /**
     * @param LeaseRequest $leaseRequest
     * @return ContractDto
     *
     * @throws Exception
     */
    public function createFromLeaseRequest(LeaseRequest $leaseRequest): ContractDto {
        $contractDto = new ContractDto();
        $contractDto->slave = $this->slavesRepository->getById($leaseRequest->slaveId);
        $contractDto->master = $this->masterRepository->getById($leaseRequest->masterId);
        $contractDto->days = $this->contractDaysDtoFactory->createFromLeaseRequest($leaseRequest);

        return $contractDto;
    }

    public function createFromLeaseContract(LeaseContract $leaseContract): ContractDto {
        $contractDto = new ContractDto();
        $contractDto->slave = $leaseContract->slave;
        $contractDto->master = $leaseContract->master;
        $contractDto->days = $this->contractDaysDtoFactory->createFromContract($leaseContract);

        return $contractDto;
    }

}