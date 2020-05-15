<?php


namespace SlaveMarket\Lease\Validator;

use SlaveMarket\Lease\Dto\LeaseRequestDto;

/**
 * Interface Для каждого валидатора, прорверяющего контракт
 * @package SlaveMarket\Lease\Validator
 */
interface LeaseValidatorInterface {
    /**
     * @param LeaseRequestDto $leaseRequestDto
     */
    public function validate(LeaseRequestDto $leaseRequestDto);

    /**
     * @return bool
     */
    public function isValid(): bool;

    /**
     * @return string[]
     */
    public function getErrors(): array;
}