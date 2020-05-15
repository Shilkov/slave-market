<?php


namespace SlaveMarket\Lease\Factory;


use DateTime;
use SlaveMarket\Lease\Dto\LeaseRequestDto;
use SlaveMarket\Lease\LeaseContractsRepository;
use SlaveMarket\Lease\LeaseRequest;

class leaseRequestDtoFactory {
    /**
     * @var ContractDtoFactory
     */
    protected $contractDtoFactory;

    /**
     * @var LeaseContractsRepository
     */
    protected $contractRepository;

    /**
     * leaseRequestDtoFactory constructor.
     * @param ContractDtoFactory $contractDtoFactory
     * @param LeaseContractsRepository $contractRepository
     */
    public function __construct(ContractDtoFactory $contractDtoFactory, LeaseContractsRepository $contractRepository) {
        $this->contractDtoFactory = $contractDtoFactory;
        $this->contractRepository = $contractRepository;
    }

    /**
     * @param LeaseRequest $leaseRequest
     * @return LeaseRequestDto
     *
     * @throws \Exception
     */
    public function createFromLeaseRequest(LeaseRequest $leaseRequest): LeaseRequestDto
    {
        $contractDto = new LeaseRequestDto();
        $contractDto->leaseRequest = $leaseRequest;
        $contractDto->contractDto = $this->contractDtoFactory->createFromLeaseRequest($leaseRequest);
        $contractDto->previousContractsDto = $this->getPreviousContractsDto($leaseRequest);

        return $contractDto;
    }

    /**
     * @param LeaseRequest $leaseRequest
     * @return array
     * @throws \Exception
     */
    protected function getPreviousContractsDto(LeaseRequest $leaseRequest): array {
        $otherContractsDto = [];
        $fromDate = new DateTime($leaseRequest->timeFrom);
        $toDate = new DateTime($leaseRequest->timeTo);
        $contracts = $this->contractRepository->getForSlave($leaseRequest->slaveId, $fromDate->format('Y-m-d'), $toDate->format('Y-m-d'));
        foreach($contracts as $contract) {
            $otherContractsDto[] = $this->contractDtoFactory->createFromLeaseContract($contract);
        }
        return $otherContractsDto;
    }
}