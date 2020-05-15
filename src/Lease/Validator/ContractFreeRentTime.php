<?php


namespace SlaveMarket\Lease\Validator;

use SlaveMarket\Lease\Dto\ContractDto;
use SlaveMarket\Lease\Dto\LeaseRequestDto;
use SlaveMarket\Lease\Validators\ContractFreeRentTimeTest;

/**
 * Тесты для класса @see ContractFreeRentTimeTest
 * @package SlaveMarket\Lease\Validator
 */
class ContractFreeRentTime extends LeaseValidatorAbstract implements LeaseValidatorInterface {

    /**
     *  Нельзя арендовать раба на выбранный период, если хотя бы один час в периоде уже занят
     *
     * @param LeaseRequestDto $leaseRequestDto
     */
    public function validate(LeaseRequestDto $leaseRequestDto) {
        $isValid = true;

        $newContract = $leaseRequestDto->contractDto;
        $previousContracts = $leaseRequestDto->previousContractsDto;
        $slave = $leaseRequestDto->contractDto->slave;
        $hoursIntersection = $this->getHoursIntersection($newContract, $previousContracts);

        if(0 < count($hoursIntersection)) {
            $isValid = false;
            $this->errors[] = sprintf('Ошибка. Раб #%d "%s" занят. Занятые часы: %s', $slave->getId(), $slave->getName(), implode(", ", $this->transformDayHourToStringList($hoursIntersection)));
        }

        $this->isValid = $isValid;
    }

    /**
     * @param ContractDto $newContract
     * @param ContractDto[] $previousContracts
     *
     * @return int[][]
     */
    protected function getHoursIntersection(ContractDto $newContract, array $previousContracts): array {
        $intersectionDays = [];
        foreach($newContract->days as $day) {
            foreach($previousContracts as $previousContract) {
                $previousContractsDay = $this->findContractDayDto($previousContract, $day->dayMark);
                $busyHours = array_intersect($day->hours, $previousContractsDay->hours);
                if(0 < count($busyHours) && $newContract->master->isVIP() <= $previousContract->master->isVIP()) {
                    $intersectionDays[$day->dayMark] = $this->hoursMerge($intersectionDays[$day->dayMark] ?? [], $busyHours);
                }
            }
        }

        return $intersectionDays;
    }

    /**
     * @param int[][] $days
     * @return string[]
     */
    protected function transformDayHourToStringList(array $days): array {
        $result = [];
        foreach($days as $dayMark => $hours) {
            foreach($hours as $hour) {
                $result[] = sprintf('"%s %02d"', $dayMark, $hour);
            }
        }

        return $result;
    }
}