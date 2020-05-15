<?php

namespace SlaveMarket\Lease\Validators;


use PHPUnit\Framework\TestCase;
use SlaveMarket\Lease\Dto\LeaseRequestDto;
use SlaveMarket\Lease\Validator\ContractTimeValidators;

/**
 * Тесты для @see ContractTimeValidators
 * @package SlaveMarket\Lease\Validators
 */
class ContractTimeValidatorsTest extends TestCase {

    use FixtureDtoTrait;

    /**
     * @var ContractTimeValidators
     */
    private $contractTimeValidators;

    protected function setUp() {
        parent::setUp();

        $this->contractTimeValidators = new ContractTimeValidators();
    }


    /**
     * @param LeaseRequestDto $leaseRequestDto
     * @param bool $isValid
     *
     * @dataProvider getValidateFixture
     */
    public function test_validate(LeaseRequestDto $leaseRequestDto, bool $isValid) {
        $this->contractTimeValidators->validate($leaseRequestDto);

        $this->assertEquals($isValid, $this->contractTimeValidators->isValid());

    }

    /**
     * @return array[]
     */
    public function getValidateFixture(): array {
        return [
            'valid single contract'         => [
                'leaseRequestDto' => $this->getLeaseRequestDtoFixture([10, 10]),
                'isValid'         => true
            ],
            'invalid valid single contract' => [
                'leaseRequestDto' => $this->getLeaseRequestDtoFixture([2, 23]),
                'isValid'         => false
            ],
            'valid with some contract'      => [
                'leaseRequestDto' => $this->getLeaseRequestDtoFixture([2, 2], [[3, 17]]),
                'isValid'         => true
            ],
            'invalid with some contract'    => [
                'leaseRequestDto' => $this->getLeaseRequestDtoFixture([2, 2], [[3, 18]]),
                'isValid'         => false
            ],
            'valid with intersection'       => [
                'leaseRequestDto' => $this->getLeaseRequestDtoFixture([4, 5], [[3, 18]]),
                'isValid'         => true
            ],
            'valid oll day long'            => [
                'leaseRequestDto' => $this->getLeaseRequestDtoFixture([0, 23]),
                'isValid'         => true
            ],
            'valid on 3 days contract'      => [
                'leaseRequestDto' => $this->getLeaseRequestDtoFixture([8, 55]),
                'isValid'         => true
            ],
        ];
    }


}