<?php


namespace SlaveMarket\Lease\Factory;


use DateTime;
use SlaveMarket\Lease\Dto\ContractDayDto;
use SlaveMarket\Lease\LeaseContract;
use SlaveMarket\Lease\LeaseRequest;

class ContractDaysDtoFactory {

    const FIRST_WORK_HOUR = 0;
    const LAST_WORK_HOUR = 23;

    /**
     * @param LeaseRequest $leaseRequest
     * @return ContractDayDto[]
     *
     * @throws \Exception
     */
    public function createFromLeaseRequest(LeaseRequest $leaseRequest): array {
        $fromDate = new DateTime($leaseRequest->timeFrom);
        $toDate = new DateTime($leaseRequest->timeTo);
        $contractDays = [];
        for($daytime = clone $fromDate; $daytime < $toDate; $daytime->modify('+1day')) {
            $contractDayDto = $this->getContractDayDto($fromDate, $toDate, $daytime);
            $contractDays[] = $contractDayDto;
        }

        return $contractDays;
    }

    /**
     * @param LeaseContract $leaseContract
     * @return ContractDayDto[]
     */
    public function createFromContract(LeaseContract $leaseContract): array {

        $params = [];
        foreach($leaseContract->leasedHours as $leasedHours) {
            $day = $leasedHours->getDate();
            $hour = $leasedHours->getHour();
            $param = $params[$day] ?? ['min' => $hour, 'max' => $hour, 'date' => $leasedHours->getDateTime()];
            $param['min'] = min($hour, $param['min']);
            $param['max'] = max($hour, $param['max']);
            $params[$day] = $param;
        }
        $contractDays = [];
        foreach($params as $param) {
            $contractDays[] = $this->create($param['min'], $param['max'], $param['date']);
        }

        return $contractDays;
    }

    /**
     * @param DateTime $fromDate
     * @param DateTime $currentDay
     * @param DateTime $toDate
     *
     * @return ContractDayDto
     */
    protected function getContractDayDto(DateTime $fromDate, DateTime $toDate, DateTime $currentDay): ContractDayDto {
        $isStartDay = $this->isCurrentDay($fromDate, $currentDay);
        $isEndDay = $this->isCurrentDay($toDate, $currentDay);
        $startHour = $isStartDay ? $fromDate->format('H') : self::FIRST_WORK_HOUR;
        $endHour = $isEndDay ? $toDate->format('H') : self::LAST_WORK_HOUR;

        return $this->create($startHour, $endHour, $currentDay);
    }

    /**
     * @param DateTime $date1
     * @param DateTime $date2
     * @return bool
     */
    protected function isCurrentDay(DateTime $date1, DateTime $date2): bool {
        $d1 = clone $date1;
        $d1->setTime(0, 0, 0);
        $d2 = clone $date2;
        $d2->setTime(0, 0, 0);
        return $date1->diff($date2)->days === 0;
    }

    /**
     * @param int $startHour
     * @param int $endHour
     * @param DateTime $currentDay
     * @return ContractDayDto
     */
    protected function create(int $startHour, int $endHour, DateTime $currentDay): ContractDayDto {
        $contractDayDto = new ContractDayDto();
        $contractDayDto->isFoolDay = self::FIRST_WORK_HOUR === $startHour && self::LAST_WORK_HOUR === $endHour;
        $contractDayDto->hours = range($startHour, $endHour);
        $contractDayDto->dayMark = $currentDay->format('Y-m-d');

        return $contractDayDto;
    }
}