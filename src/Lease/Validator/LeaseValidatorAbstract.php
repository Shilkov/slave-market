<?php


namespace SlaveMarket\Lease\Validator;

use SlaveMarket\Lease\Dto\ContractDayDto;
use SlaveMarket\Lease\Dto\ContractDto;

/**
 * Общий клас для валидаторов реализующий получение ошибок и метки валидности
 * @package SlaveMarket\Lease\Validator
 */
abstract class LeaseValidatorAbstract implements LeaseValidatorInterface {

    /**
     * @var bool
     */
    protected $isValid = false;
    /**
     * @var string[]
     */
    protected $errors = [];

    /**
     * @return bool
     */
    public function isValid(): bool {
        return $this->isValid;
    }

    /**
     * @return array|string[]
     */
    public function getErrors(): array {
        return $this->errors;
    }

    /**
     * находим день в контракте по метке дня
     *
     * @param ContractDto $contractDto
     * @param string $dayMark
     *
     * @return ContractDayDto|null
     */
    protected function findContractDayDto(ContractDto $contractDto, string $dayMark): ?ContractDayDto {
        $return = null;
        foreach($contractDto->days as $contractDayDto) {
            if($contractDayDto->dayMark === $dayMark) {
                $return = $contractDayDto;

                break;
            }
        }

        return $return;
    }

    /**
     * объединение часов
     *
     * @param int[] $hours1
     * @param int[] $hours2
     * @return int[]
     */
    protected function hoursMerge(array $hours1, array $hours2): array {
        $hours = array_merge($hours1, $hours2);

        return array_unique($hours);
    }
}