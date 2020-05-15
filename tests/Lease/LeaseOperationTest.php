<?php

namespace SlaveMarket\Lease;

use PHPUnit\Framework\TestCase;
use Prophecy\Prophecy\ObjectProphecy;
use SlaveMarket\Lease\Factory\ContractDaysDtoFactory;
use SlaveMarket\Lease\Factory\ContractDtoFactory;
use SlaveMarket\Lease\Factory\ContractFactory;
use SlaveMarket\Lease\Factory\leaseRequestDtoFactory;
use SlaveMarket\Lease\Validator\ContractFreeRentTime;
use SlaveMarket\Lease\Validator\ContractTimeValidators;
use SlaveMarket\Lease\Validator\ContractValidatorsHandler;
use SlaveMarket\Master;
use SlaveMarket\MastersRepository;
use SlaveMarket\Slave;
use SlaveMarket\SlavesRepository;

/**
 * Тесты операции аренды раба
 *
 * @package SlaveMarket\Lease
 */
class LeaseOperationTest extends TestCase {

    /**
     * @var ObjectProphecy
     */
    private $mastersRepository;
    /**
     * @var ObjectProphecy
     */
    private $slavesRepository;
    /**
     * @var ObjectProphecy
     */
    private $contractsRepo;
    /**
     * @var LeaseOperation
     */
    private $leaseOperation;

    protected function setUp() {
        parent::setUp();
        $this->mastersRepository = $this->prophesize(MastersRepository::class);
        /** @var MastersRepository $mastersRepository */
        $mastersRepository = $this->mastersRepository->reveal();
        $this->slavesRepository = $this->prophesize(SlavesRepository::class);
        /** @var SlavesRepository $slaveRepository */
        $slaveRepository = $this->slavesRepository->reveal();
        $this->contractsRepo = $this->prophesize(LeaseContractsRepository::class);
        /** @var LeaseContractsRepository $contractsRepo */
        $contractsRepo = $this->contractsRepo->reveal();
        $validatorsHandler = new ContractValidatorsHandler([
            new ContractFreeRentTime(),
            new ContractTimeValidators(),
        ]);
        $contractDaysDtoFactory = new ContractDaysDtoFactory();
        $contractDtoFactory = new ContractDtoFactory($contractDaysDtoFactory, $mastersRepository, $slaveRepository);
        $leaseRequestDtoFactory = new LeaseRequestDtoFactory($contractDtoFactory, $contractsRepo);

        $contractFactory = new ContractFactory();
        $this->leaseOperation = new LeaseOperation($validatorsHandler, $leaseRequestDtoFactory, $contractFactory);
    }

    /**
     * Stub репозитория хозяев
     *
     * @param Master ...$masters
     */
    private function addMasterFixture(...$masters): void {
        foreach($masters as $master) {
            $this->mastersRepository->getById($master->getId())->willReturn($master);
        }
    }

    /**
     * Stub репозитория рабов
     *
     * @param Slave ...$slaves
     */
    private function addSlaveFixture(...$slaves): void {
        foreach($slaves as $slave) {
            $this->slavesRepository->getById($slave->getId())->willReturn($slave);
        }
    }

    /**
     * Если раб занят, то арендовать его не получится
     */
    public function test_periodIsBusy_failedWithOverlapInfo() {
        // -- Arrange
        {
            // Хозяева
            $master1 = new Master(1, 'Господин Боб');
            $master2 = new Master(2, 'сэр Вонючка');
            $this->addMasterFixture($master1, $master2);

            // Раб
            $slave1 = new Slave(1, 'Уродливый Фред', 20);
            $this->addSlaveFixture($slave1);

            // Договор аренды. 1й хозяин арендовал раба
            $leaseContract1 = new LeaseContract($master1, $slave1, 80, [
                new LeaseHour('2017-01-01 00'),
                new LeaseHour('2017-01-01 01'),
                new LeaseHour('2017-01-01 02'),
                new LeaseHour('2017-01-01 03'),
            ]);

            // Stub репозитория договоров
            $this->contractsRepo->getForSlave($slave1->getId(), '2017-01-01', '2017-01-01')->willReturn([$leaseContract1]);

            // Запрос на новую аренду. 2й хозяин выбрал занятое время
            $leaseRequest = new LeaseRequest();
            $leaseRequest->masterId = $master2->getId();
            $leaseRequest->slaveId = $slave1->getId();
            $leaseRequest->timeFrom = '2017-01-01 01:30:00';
            $leaseRequest->timeTo = '2017-01-01 02:01:00';
        }

        // -- Act
        $response = $this->leaseOperation->run($leaseRequest);

        // -- Assert
        $expectedErrors = ['Ошибка. Раб #1 "Уродливый Фред" занят. Занятые часы: "2017-01-01 01", "2017-01-01 02"'];

        $this->assertArraySubset($expectedErrors, $response->getErrors());
        $this->assertNull($response->getLeaseContract());
    }

    /**
     * Если раб бездельничает, то его легко можно арендовать
     */
    public function test_idleSlave_successfullyLeased() {
        // -- Arrange
        {
            // Хозяева
            $master1 = new Master(1, 'Господин Боб');
            $this->addMasterFixture($master1);

            // Раб
            $slave1 = new Slave(1, 'Уродливый Фред', 20);
            $this->addSlaveFixture($slave1);

            $this->contractsRepo->getForSlave($slave1->getId(), '2017-01-01', '2017-01-01')->willReturn([]);

            // Запрос на новую аренду
            $leaseRequest = new LeaseRequest();
            $leaseRequest->masterId = $master1->getId();
            $leaseRequest->slaveId = $slave1->getId();
            $leaseRequest->timeFrom = '2017-01-01 01:30:00';
            $leaseRequest->timeTo = '2017-01-01 02:01:00';
        }

        // -- Act
        $response = $this->leaseOperation->run($leaseRequest);

        // -- Assert
        $this->assertEmpty($response->getErrors());
        $this->assertInstanceOf(LeaseContract::class, $response->getLeaseContract());
        $this->assertEquals(40, $response->getLeaseContract()->price);
    }
}