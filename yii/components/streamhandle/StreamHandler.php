<?php
namespace app\components\streamhandle;

class StreamHandler
{
    private $maxStreams = 18;
    private $timeout = 10;
    private $streams = [];
    private $handles = [];
    private $all_pipes = [];
    private $result = [];
    private $id = 0;


    public function openProc($url, $accID)
    {
        $urls = implode(',', $url);
        $id = ++$this->id;
        $error_log = "error" . $id . ".txt";
        $descriptorspec = array(
            0 => array("pipe", "r"),
            1 => array("pipe", "w"),
            2 => array("file", $error_log, "w")
        );
        $cmd = 'php yii parse/page \'' . $urls . '\'  \'' . $accID . '\']';
//        $cmd = '$this->action('.$delay.')';
        $this->handles[$id] = proc_open($cmd, $descriptorspec, $pipes);

        $this->streams[$id] = $pipes[1];
        $this->all_pipes[$id] = $pipes;
    }

    public function getResult()
    {
        $return = [];
        if ($this->result) {
            foreach ($this->result as $apps) {
                $result = unserialize($apps);
                if ($result) {
                    foreach ($result as $app)
                        $return[] = $app;
                }
            }
            $this->result = [];
            return $return;
        }
    }

    public function allowNewStream()
    {
        $currentNum = count($this->streams);
        $maxNum = $this->maxStreams;
        if (!($currentNum > $maxNum)) {
            return true;
        }
        return false;
    }

    public function eventListen()
    {
        if (count($this->streams)) {
            $read = $this->streams;
            $w = null;
            $e = null;
            stream_select($read, $w, $e, $this->timeout);
            $this->eventProcess($read);
        }
        return $this->getResult();
    }

    private function eventProcess($read)
    {
        foreach ($read as $r) {
            $id = array_search($r, $this->streams);

            $this->result[] = stream_get_contents($this->all_pipes[$id][1]);
            if (feof($r)) {
//                fclose($this->all_pipes[$id][0]);
                fclose($this->all_pipes[$id][1]);
                $return_value = proc_close($this->handles[$id]);
                unset($this->handles[$id]);
                unset($this->streams[$id]);
                echo "\n\r" . $id . ' done';
            }
        }
    }

    private function streamCheck()
    {
        $read = $this->streams;
        $w = null;
        $e = null;
        stream_select($read, $w, $e, $this->timeout);
        $this->eventProcess($read);
    }

    private function waitToFinish()
    {
        /* Пояснение - Пока число запущенных потоков НЕ МЕНЬШЕ максимально возможного числа потоков */
        while (!(count($this->streams) < $this->maxStreams)) {
            $this->streamCheck();
        }
    }

    public function waitToFinishAll()
    {
        while (count($this->streams)) {
            $this->streamCheck();
        }
    }

    public function runStream($url, $accountId)
    {
        if (!$this->allowNewStream()) {
            $this->waitToFinish();
        }
        $this->openProc($url, $accountId);

    }
}
