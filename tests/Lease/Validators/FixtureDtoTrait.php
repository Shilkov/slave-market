<?php


namespace SlaveMarket\Lease\Validators;

use SlaveMarket\Lease\Dto\ContractDayDto;
use SlaveMarket\Lease\Dto\ContractDto;
use SlaveMarket\Lease\Dto\LeaseRequestDto;
use SlaveMarket\Lease\LeaseRequest;
use SlaveMarket\Master;
use SlaveMarket\Slave;

/**
 * Трэйт по созданию фикстур для тестов валидаторов
 * @package SlaveMarket\Lease\Validators
 */
trait FixtureDtoTrait {

    /**
     * @param array $contractData [$hourStart, $hourEnd, ?isMasterVip]
     * @param array $previousContractsData [[$hourStart, $hourEnd, ?isMasterVip]]
     * @return LeaseRequestDto
     */
    protected function getLeaseRequestDtoFixture(array $contractData, array $previousContractsData = []): LeaseRequestDto {
        $contractDto = $this->getContract($contractData);
        $previousContracts = $this->getPreviousContract($previousContractsData);
        $leaseRequest = $this->getLeaseRequest();

        return $this->getLeaseRequestDto($leaseRequest, $contractDto, $previousContracts);
    }


    /**
     * @param int $hourStart
     * @param int $hourEnd
     * @return ContractDayDto[]
     */
    protected function getContractDayDto(int $hourStart, int $hourEnd): array {
        $days = [];
        $result = [];

        for($hour = $hourStart; $hour <= $hourEnd; $hour++) {
            $day = ($hour / 24) + 1;
            $days[sprintf('1-1-%d', $day)][] = $hour % (24 * $day);
        }

        foreach($days as $key => $hours) {
            $contractDay = new ContractDayDto();
            $contractDay->isFoolDay = 24 === count($hours);
            $contractDay->hours = $hours;
            $contractDay->dayMark = $key;
            $result[] = $contractDay;
        }

        return $result;
    }

    /**
     * @param Slave $slave
     * @param Master $masterNoVip
     * @param ContractDayDto[] $contractDays
     *
     * @return ContractDto
     */
    protected function getContractDto(Slave $slave, Master $masterNoVip, array $contractDays): ContractDto {
        $contractDto = new ContractDto();
        $contractDto->days = $contractDays;
        $contractDto->slave = $slave;
        $contractDto->master = $masterNoVip;

        return $contractDto;
    }

    /**
     * @param LeaseRequest $leaseRequest
     * @param ContractDto $contractDto
     * @param array $otherContractsDto
     * @return LeaseRequestDto
     */
    protected function getLeaseRequestDto(LeaseRequest $leaseRequest, ContractDto $contractDto, array $otherContractsDto): LeaseRequestDto {
        $leaseRequestDto = new LeaseRequestDto();
        $leaseRequestDto->leaseRequest = $leaseRequest;
        $leaseRequestDto->contractDto = $contractDto;
        $leaseRequestDto->previousContractsDto = $otherContractsDto;
        return $leaseRequestDto;
    }

    /**
     * @return LeaseRequest
     */
    protected function getLeaseRequest(): LeaseRequest {
        return new LeaseRequest();
    }

    /**
     * @param array $contractData [$hourStart, $hourEnd, ?isMasterVip]
     *
     * @return ContractDto
     */
    protected function getContract(array $contractData): ContractDto {
        list($hourStart, $hourEnd, $isMasterVip) = $contractData;
        $slave = new Slave(1, 'Test Slave', 10);
        $master = new Master(1, 'Master', $isMasterVip ?? false);
        $contractDay = $this->getContractDayDto($hourStart, $hourEnd);

        return $this->getContractDto($slave, $master, $contractDay);
    }

    /**
     * @param array $previousContractsHour [$hourStart, $hourEnd, ?isMasterVip]
     * @return array
     */
    protected function getPreviousContract(array $previousContractsHour): array {
        $previousContracts = [];
        foreach($previousContractsHour as $previousContractHour) {
            $previousContracts[] = $this->getContract($previousContractHour);
        }

        return $previousContracts;
    }
}