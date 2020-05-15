<?php


namespace SlaveMarket\Lease\Dto;


class ContractDayDto {
    /**
     * метка дня
     * @var string Y-m-d
     */
    public $dayMark;
    /**
     * метка того что раб занят на весь день
     * @var bool
     */
    public $isFoolDay;
    /**
     * массив занятых часов в день
     * @var int[]
     */
    public $hours;
}