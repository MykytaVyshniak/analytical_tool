<?php
class AnalyticalTool {
    private $records = [];

    public function addRecord($record) {
        $this->records[] = $record;
    }

    public function averageWaitingTime($service, $questionType, $responseType, $dateFrom, $dateTo) {
        $totalTime = 0;
        $count = 0;
        foreach ($this->records as $record) {
            if ($record->matchesCriteria($service, $questionType, $responseType, $dateFrom, $dateTo)) {
                $totalTime += $record->time;
                $count++;
            }
        }

        return $count > 0 ? round($totalTime / $count) : '-';
    }
}

class Record {
    public $service;
    public $questionType;
    public $responseType;
    public $date;
    public $time;

    public function __construct($line) {
        $data = explode(" ", $line);
        $this->service = $data[1];
        $this->questionType = $data[2];
        $this->responseType = $data[3];
        $this->date = $data[4];
        $this->time = intval($data[5]);
    }

    public function matchesCriteria($service, $questionType, $responseType, $dateFrom, $dateTo) {
        $thisService = explode('.', $this->service)[0];
        $service = explode('.', $service)[0];
        if ($service !== '*' && $thisService !== $service) {
            return false;
        }

        $thisQuestionType = explode('.', $this->questionType)[0];
        $questionType = explode('.', $questionType)[0];
        if ($questionType !== '*' && $thisQuestionType !== $questionType) {
            return false;
        }

        if ($responseType !== '*' && $this->responseType !== $responseType) {
            return false;
        }

        if (!empty($dateFrom) && !empty($dateTo)) {
            $recordDate = strtotime($this->date);
            $fromDate = strtotime($dateFrom);
            $toDate = strtotime($dateTo . ' +1 day');
            if ($recordDate < $fromDate || $recordDate >= $toDate) {
                return false;
            }
        }

        return true;
    }
}

$filename = "input.txt";
$input = file($filename, FILE_IGNORE_NEW_LINES);

$analyticalTool = new AnalyticalTool();
for ($i = 1; $i < count($input); $i++) {
    if (substr($input[$i], 0, 1) === 'C') {
        $record = new Record($input[$i]);
        $analyticalTool->addRecord($record);
    } else if (substr($input[$i], 0, 1) === 'D') {
        $query = explode(" ", $input[$i]);
        $service = $query[1];
        $questionType = $query[2];
        $responseType = $query[3];
        $dates = explode("-", $query[4]);
        $dateFrom = $dates[0];
        $dateTo = isset($dates[1]) ? $dates[1] : null;
        echo $analyticalTool->averageWaitingTime($service, $questionType, $responseType, $dateFrom, $dateTo) . PHP_EOL;
    }
}
?>