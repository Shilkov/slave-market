<?php

namespace SlaveMarket\Lease\Validators;

use PHPUnit\Framework\TestCase;
use PHPUnit_Framework_MockObject_MockObject;
use SlaveMarket\Lease\Dto\LeaseRequestDto;
use SlaveMarket\Lease\Validator\ContractValidatorsHandler;
use SlaveMarket\Lease\Validator\LeaseValidatorInterface;

/**
 * Тэсты для класса @see ContractValidatorsHandler
 * @package SlaveMarket\Lease\Validators
 */
class ContractValidatorsHandlerTest extends TestCase {

    /**
     * @var ContractValidatorsHandler
     */
    private $contractValidatorsHandler;
    /**
     * @var PHPUnit_Framework_MockObject_MockObject|LeaseValidatorInterface
     */
    private $firstValidator;
    /**
     * @var PHPUnit_Framework_MockObject_MockObject|LeaseValidatorInterface
     */
    private $secondValidator;

    protected function setUp() {
        parent::setUp();
        $validators[] = $this->firstValidator = $this->createMock(LeaseValidatorInterface::class);
        $validators[] = $this->secondValidator = $this->createMock(LeaseValidatorInterface::class);

        $this->contractValidatorsHandler = new ContractValidatorsHandler($validators);
    }

    /**
     * проверяем вызов каждого дочернего валидатора
     */
    public function test_validate() {
        $leaseRequestDto = new LeaseRequestDto();
        $this->firstValidator
            ->expects($this->once())
            ->method('validate')
            ->with($leaseRequestDto)
        ;

        $this->secondValidator
            ->expects($this->once())
            ->method('validate')
            ->with($leaseRequestDto)
        ;

        $this->contractValidatorsHandler->validate($leaseRequestDto);
    }

    /**
     * проверяем возвращение ошибки
     * @param bool $isFirstValid
     * @param bool $isSecondValid
     * @param bool $result
     *
     * @dataProvider isValidDataProvider
     */
    public function test_isValid(bool $isFirstValid, bool $isSecondValid, bool $result) {
        $this->firstValidator
            ->expects($this->once())
            ->method('isValid')
            ->willReturn($isFirstValid)
        ;

        $this->secondValidator
            ->expects($this->once())
            ->method('isValid')
            ->willReturn($isSecondValid)
        ;

        $actual = $this->contractValidatorsHandler->isValid();

        $this->assertEquals($result, $actual);
    }

    /**
     * @return array| bool[][]
     */
    public function isValidDataProvider(): array {
        return [
            'valid'   => [
                'isFirstValid'  => true,
                'isSecondValid' => true,
                'result'        => true,
            ],
            'invalid' => [
                'isFirstValid'  => true,
                'isSecondValid' => false,
                'result'        => false,
            ],
        ];
    }

    /**
     * проверяем возвращение ошибки
     * @param string[] $getErrorsFirst
     * @param string[] $getErrorsSecond
     * @param string[] $result
     *
     * @dataProvider getErrorsDataProvider
     */
    public function test_getErrors(array $getErrorsFirst, array $getErrorsSecond, array $result) {
        $this->firstValidator
            ->expects($this->once())
            ->method('getErrors')
            ->willReturn($getErrorsFirst)
        ;

        $this->secondValidator
            ->expects($this->once())
            ->method('getErrors')
            ->willReturn($getErrorsSecond)
        ;

        $actual = $this->contractValidatorsHandler->getErrors();

        $this->assertEquals($result, $actual);
    }

    /**
     * @return array|string[][]
     */
    public function getErrorsDataProvider(): array {
        return [
            'no error'  => [
                'getErrorsFirst' => [],
                'isSecondValid'  => [],
                'result'         => [],
            ],
            'has error' => [
                'getErrorsFirst'  => ['first error'],
                'getErrorsSecond' => ['second error 1', 'second error 2'],
                'result'          => ['first error', 'second error 1', 'second error 2'],
            ],
        ];
    }

}