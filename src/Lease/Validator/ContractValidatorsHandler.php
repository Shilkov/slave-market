<?php


namespace SlaveMarket\Lease\Validator;


use SlaveMarket\Lease\Dto\LeaseRequestDto;

class ContractValidatorsHandler implements LeaseValidatorInterface {

    /**
     * @var LeaseValidatorInterface[]
     */
    protected $handlers;

    /**
     * ContractValidatorsHandler constructor.
     * @param LeaseValidatorInterface[] $handlers
     */
    public function __construct(array $handlers) {
        $this->handlers = $handlers;
    }

    public function validate(LeaseRequestDto $leaseRequestDto) {
        foreach($this->handlers as $handler) {
            $handler->validate($leaseRequestDto);
        }
    }

    public function isValid(): bool {
        $isValid = true;
        foreach($this->handlers as $handler) {
            $isValid = $handler->isValid() && $isValid;
        }

        return $isValid;
    }

    public function getErrors(): array {
        $errors = [];
        foreach($this->handlers as $handler) {
            $errors = array_merge($errors, $handler->getErrors());
        }

        return $errors;
    }
}