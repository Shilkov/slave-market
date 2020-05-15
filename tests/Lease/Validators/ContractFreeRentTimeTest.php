<?php


namespace SlaveMarket\Lease\Validators;


use PHPUnit\Framework\TestCase;
use SlaveMarket\Lease\Dto\LeaseRequestDto;
use SlaveMarket\Lease\Validator\ContractFreeRentTime;

class ContractFreeRentTimeTest extends TestCase {

    use FixtureDtoTrait;

    /**
     * @var ContractFreeRentTime
     */
    private $contractFreeRentTime;

    protected function setUp() {
        parent::setUp();

        $this->contractFreeRentTime = new ContractFreeRentTime();
    }

    /**
     * @param LeaseRequestDto $leaseRequestDto
     * @param bool $isValid
     * @param string[] $errors
     *
     * @dataProvider getValidateFixture
     */
    public function test_validate(LeaseRequestDto $leaseRequestDto, bool $isValid, array $errors) {
        $this->contractFreeRentTime->validate($leaseRequestDto);

        $this->assertEquals($isValid, $this->contractFreeRentTime->isValid());
        $this->assertEquals($errors, $this->contractFreeRentTime->getErrors());

    }

    /**
     * @return array[]
     */
    public function getValidateFixture(): array {
        return [
//            'valid single contract'         => [
//                'leaseRequestDto' => $this->getLeaseRequestDtoFixture([10, 10]),
//                'isValid'         => true,
//                'errors'          => [],
//            ],
//            'valid with some contract'      => [
//                'leaseRequestDto' => $this->getLeaseRequestDtoFixture([2, 2], [[3, 17]]),
//                'isValid'         => true,
//                'errors'          => [],
//            ],
            'invalid with intersection'     => [
                'leaseRequestDto' => $this->getLeaseRequestDtoFixture([2, 4], [[3, 10]]),
                'isValid'         => false,
                'errors'          => ['Ошибка. Раб #1 "Test Slave" занят. Занятые часы: "1-1-1 03", "1-1-1 04"'],
            ],
//            'valid Vip with intersection'   => [
//                'leaseRequestDto' => $this->getLeaseRequestDtoFixture([2, 2, true], [[0, 23]]),
//                'isValid'         => true,
//                'errors'          => [],
//            ],
//            'invalid Vip with intersection' => [
//                'leaseRequestDto' => $this->getLeaseRequestDtoFixture([2, 2, true], [[0, 23, true]]),
//                'isValid'         => false,
//                'errors'          => ['Ошибка. Раб #1 "Test Slave" занят. Занятые часы: "1-1-1 02"'],
//            ],
        ];
    }
}