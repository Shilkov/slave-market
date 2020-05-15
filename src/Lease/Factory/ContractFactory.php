<?php


namespace SlaveMarket\Lease\Factory;


use SlaveMarket\Lease\Dto\ContractDayDto;
use SlaveMarket\Lease\Dto\ContractDto;
use SlaveMarket\Lease\LeaseContract;
use SlaveMarket\Lease\LeaseHour;

class ContractFactory {

    /**
     * превращает ДТО в контракт
     *
     * @param ContractDto $contractDto
     *
     * @return LeaseContract
     *
     * @throws \Exception
     */
    public function createFromDto(ContractDto $contractDto) {
        $slave = $contractDto->slave;
        $master = $contractDto->master;
        $price = $this->getPrice($contractDto, $slave->getPricePerHour());
        $leasedHours = $this->getLeasedHours($contractDto);

        return new LeaseContract($master, $slave, $price, $leasedHours);
    }

    /**
     * @param ContractDto $contractDto
     * @param float $pricePerHour
     *
     * @return float
     */
    protected function getPrice(ContractDto $contractDto, float $pricePerHour): float {
        $price = 0;
        foreach($contractDto->days as $day) {
            $price += $pricePerHour * $this->getPaidHours($day);
        }
        return $price;
    }

    /**
     * @param ContractDayDto $day
     *
     * @return int
     */
    protected function getPaidHours(ContractDayDto $day): int {
        return $day->isFoolDay ? 16 : count($day->hours);
    }

    /**
     * @param ContractDto $contractDto
     *
     * @return LeaseHour[]
     *
     * @throws \Exception
     */
    protected function getLeasedHours(ContractDto $contractDto): array {
        $leasedHours = [];
        foreach($contractDto->days as $day) {
            foreach($day->hours as $hour) {
                $leasedHours[] = new LeaseHour(sprintf('%s %d:00', $day->dayMark, $hour));
            }
        }

        return $leasedHours;
    }
}