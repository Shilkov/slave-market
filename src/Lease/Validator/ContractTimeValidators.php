<?php


namespace SlaveMarket\Lease\Validator;


use SlaveMarket\Lease\Dto\ContractDayDto;
use SlaveMarket\Lease\Dto\ContractDto;
use SlaveMarket\Lease\Dto\LeaseRequestDto;
use SlaveMarket\Lease\Validators\ContractTimeValidatorsTest;

/**
 * Тесты для данного класса @see ContractTimeValidatorsTest
 * @package SlaveMarket\Lease\Validators
 */
class ContractTimeValidators extends LeaseValidatorAbstract implements LeaseValidatorInterface {

    /**
     * максимальное количество часов в сутки
     */
    const MAX_WORK_HOUR_IN_DAY = 16;

    /**
     * Проверяем создаваемый контракт на соответствие правилу "Рабы не могут работать больше 16 часов в сутки."
     *
     * @param LeaseRequestDto $leaseRequestDto
     */
    public function validate(LeaseRequestDto $leaseRequestDto) {
        $isValid = true;
        $slave = $leaseRequestDto->contractDto->slave;
        $busyDays = $this->getDaysHours($leaseRequestDto->contractDto, $leaseRequestDto->previousContractsDto);

        foreach($busyDays as $dayMark => $day) {
            $hourInDay = count($day);
            if($hourInDay > self::MAX_WORK_HOUR_IN_DAY) {
                $isValid = false;
                $this->errors[] = sprintf('Ошибка. Раб #%d "%s" %s работает %d часов', $slave->getId(), $slave->getName(), $dayMark, $hourInDay);
            }
        }

        $this->isValid = $isValid;
    }

    /**
     * Находим сколько часов работает раб в сутки с учётом других его контрактов
     *
     * @param ContractDto $contractDto
     * @param ContractDto[] $otherContractsDto
     *
     * @return int[][]
     */
    protected function getDaysHours(ContractDto $contractDto, array $otherContractsDto): array {
        $busyDays = [];

        $dayMarks = $this->getDayMarksOfNotFilledDay($contractDto->days);
        $contractsDto = $this->mergeContracts($otherContractsDto, $contractDto);

        foreach($dayMarks as $dayMark) {
            $busyDays[$dayMark] = $this->getContractsHoursForDay($contractsDto, $dayMark);
        }

        return $busyDays;
    }

    /**
     * Получаем метки не заполненых рабочих дней
     *
     * @param ContractDayDto[] $dayKey
     *
     * @return string[]
     */
    protected function getDayMarksOfNotFilledDay(array $dayKey): array {
        $dayKey = array_filter($dayKey, function (ContractDayDto $day) {
            return !$day->isFoolDay;
        });
        $dayKey = array_map(function (ContractDayDto $day) {
            return $day->dayMark;
        }, $dayKey);

        return $dayKey;
    }

    /**
     * добавляем к старым контрактом новый, чтобы проверять всю пачку
     *
     * @param ContractDto[] $otherContractsDto
     * @param ContractDto $contractDto
     *
     * @return ContractDto[]
     */
    protected function mergeContracts(array $otherContractsDto, ContractDto $contractDto): array {
        $contractsDto = $otherContractsDto;
        $contractsDto[] = $contractDto;
        return $contractsDto;
    }

    /**
     * находим часы по контрактам на заданный день
     *
     * @param ContractDto[] $contractsDto
     * @param string $dayMark Y-m-d
     *
     * @return int[]
     */
    protected function getContractsHoursForDay(array $contractsDto, string $dayMark): array {
        $totalHours = [];

        foreach($contractsDto as $contractDto) {
            $contractHours = $this->getContractHoursForDay($contractDto, $dayMark);
            $totalHours = $this->hoursMerge($totalHours, $contractHours);
        }

        return $totalHours;
    }

    /**
     * находим часы по контракту на заданный день
     *
     * @param ContractDto $contractDto
     * @param string $dayMark
     *
     * @return int[]
     */
    protected function getContractHoursForDay(ContractDto $contractDto, string $dayMark): array {
        $markedDay = $this->findContractDayDto($contractDto, $dayMark);

        return null !== $markedDay ? $markedDay->hours : [];
    }
}