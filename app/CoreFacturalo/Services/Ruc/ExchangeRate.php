<?php

namespace App\CoreFacturalo\Services\Ruc;

class ExchangeRate
{
    /**
     * @var date
     */
    private $lastDate;
    /**
     * @var Crawler
     */
    private $crawler;
    /**
     * @var Client
     */
    private $client;
    /**
     * @var string
     */
    private $curMonth;
    /**
     * @var int
     */
    private $curYear;
    /**
     * @var Crawler
     */
    private $error;

    /**
     * ExchangeRate constructor.
     */
    public function __construct($curDate, $lastDate)
    {
        $this->client = new Client();
        $this->curDate = Carbon::parse($curDate);
        $this->crawler = $this->getCrawler();
        $this->curMonth = $this->curDate->month;
        $this->curYear = $this->curDate->year;
        $this->lastDate = null;
        if ($lastDate && $curDate > $lastDate) {
            $this->lastDate = Carbon::parse($lastDate);
        }
    }

    private function getCrawler()
    {
        return $this->client->request('GET', 'https://e-consulta.sunat.gob.pe/cl-at-ittipcam/tcS01Alias');
    }

    public function get()
    {
        $exchangeRates = $this->getExchangeRates();
        $filterExchangeRates = $this->getFilterExchangeRates($exchangeRates);
        if (!$filterExchangeRates) {
            $this->error = 'No hay tipos de cambio para registrar';
            return false;
        }
        return $filterExchangeRates;
    }

    /**
     * Get Last error message.
     *
     * @return string
     */
    public function getError()
    {
        return $this->error;
    }


    private function changeExchangeRatesMonthAndYear() {
        $form = $this->crawler->filter('[name=selectForm]')->first()->form();
        $this->crawler = $this->client->submit($form, [
            'mes' => $this->curMonth,
            'anho' => $this->curYear
        ]);
    }
    private function getExchangeRates() {
        $tempCurDate = Carbon::parse($this->curDate->toDateString());
        $exchangeRates = $this->getExchangeRateFromMonthAndYear();
        reset($exchangeRates);
        if (key($exchangeRates) > $this->curDate->toDateString()) {
            $exchangeRates = [];
        }
        if (empty($exchangeRates) || ($this->lastDate && $this->curDate->month != $this->lastDate->month)) {
            $tempCurDate->subMonthNoOverflow();
            $this->curMonth = $tempCurDate->month;
            $this->curYear = $tempCurDate->year;
            $exchangeRates += $this->getExchangeRateFromMonthAndYear();
        }
        $tempCurDate->subMonthNoOverflow();
        if ($this->lastDate && $tempCurDate->month != $this->lastDate->month) {
            $firstDateToInsert = $this->lastDate->toDateString();
            $lastDateToInsert = $tempCurDate->toDateString();
            $period = CarbonPeriod::create($firstDateToInsert, '1 month', $lastDateToInsert);
            foreach ($period as $date) {
                $this->curMonth = $date->month;
                $this->curYear = $date->year;
                $exchangeRates += $this->getExchangeRateFromMonthAndYear();
            }
        }
        ksort($exchangeRates);
        reset($exchangeRates);
        if ($this->lastDate && key($exchangeRates) > $this->lastDate->toDateString()) {
            $tempLastDate = Carbon::parse($this->lastDate->toDateString());
            $tempLastDate->subMonthNoOverflow();
            $this->curMonth = $tempLastDate->month;
            $this->curYear = $tempLastDate->year;
            $exchangeRates += $this->getExchangeRateFromMonthAndYear();
            ksort($exchangeRates);
        }
        return $exchangeRates;
    }

    private function getExchangeRateFromMonthAndYear() {
        $this->changeExchangeRatesMonthAndYear();
        $this->crawler->filter('table')->each(function($node,$i) use (&$result) {
            if ($i == 1) {
                $node->filter('tr')->each(function ($tr, $j) use (&$result) {
                    if ($j > 0) {
                        $date = '';
                        $buy = '';
                        $sell = '';
                        $tr->filter('td')->each(function ($td, $k) use (&$result,&$date,&$buy,&$sell) {
                            if ($k%3==0) {
                                $date = $this->curYear.'-'.str_pad(trim($this->curMonth), 2, "0", STR_PAD_LEFT).'-'.str_pad(trim($td->text()), 2, "0", STR_PAD_LEFT);
                            } elseif ($k%3==1) {
                                $buy = trim($td->text());
                            } else {
                                $sell = trim($td->text());
                            }
                            $result[$date] = [
                                'date' => $date,
                                'buy' => $buy,
                                'sell' => $sell,
                            ];
                            $k++;
                        });
                    }
                    $j++;
                });
            }
            $i++;
        });
        return $result;
    }

    private function getNearExchangeRate($exchangeRates,$dateString) {
        $result = [];
        $date = Carbon::parse($dateString);
        while (empty($result)) {
            if (array_key_exists($date->toDateString(),$exchangeRates)) {
                $result = $exchangeRates[$date->toDateString()];
                $result['date'] = $this->curDate->toDateString();
                $result['date_original'] = $date->toDateString();
            }
            $date = $date->subDay();
        }
        return $result;
    }

    private function getFilterExchangeRates($exchangeRates) {
        $filterExchangeRates = [];
        if (!$this->lastDate) {
            $filterExchangeRates[$this->curDate->toDateString()] = $this->getNearExchangeRate($exchangeRates,$this->curDate->toDateString());
        } else {
            $completeExchangeRates = [];
            $firstDateToInsert = $this->lastDate->toDateString();
            $lastDateToInsert = $this->curDate->toDateString();
            $period = CarbonPeriod::create($firstDateToInsert, $lastDateToInsert);
            $lastExchangeRate = [];
            foreach ($period as $date) {
                if (array_key_exists($date->format('Y-m-d'), $exchangeRates)) {
                    $lastExchangeRate = $exchangeRates[$date->format('Y-m-d')];
                    $lastExchangeRate['date_original'] = $date->format('Y-m-d');
                }
                if (empty($lastExchangeRate)) {
                    $lastExchangeRate = $this->getNearExchangeRate($exchangeRates,$this->lastDate->toDateString());
                }
                $lastExchangeRate['date'] = $date->format('Y-m-d');
                $completeExchangeRates[$date->format('Y-m-d')] = $lastExchangeRate;
            }
            $filterExchangeRates = $completeExchangeRates;
        }
        return $filterExchangeRates;
    }
}
