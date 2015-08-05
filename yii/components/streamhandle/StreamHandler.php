<?php
namespace app\components\streamhandle;

class StreamHandler
{
    /**
     * Максимальное допустимое количество потоков
     * @var int
     */
    private $maxStreams = 15;
    private $timeout = 10;

    /**
     * Массив со спецификацией каналов ввода-вывода для открываемого потока.
     * @var array
     */
    private $descriptorSpec = [
        0 => array("pipe", "r"),
        1 => array("pipe", "w")
    ];


    /**
     * Массив ресурсов, в данном случае каждый поток содержит в этой
     * переменной ресурс вывода информации из потока( т.е канал
     * для записи (w) внутри потока )
     * @var array
     */
    private $streams = [];

    /**
     * Массив ресурсов открытых процессов
     * @var array
     */
    private $handles = [];

    /**
     * Массив $all_pipes содержит в себе как канал ввода, так и канал вывода
     * из потока.
     * @var array
     */
    private $all_pipes = [];

    /**
     * Массив, в который записываются данные перед закрытием потока.
     * @var array
     */
    private $result = [];

    /**
     * Уникальный идентификатор процесса,
     * каждый открытый процесс создает ++$id.
     * @var int
     */
    private $id = 0;


    /**
     * Добваляем в дескрипторы пути до вывода ошибок.
     * @param $id
     */
    private function setErrorLog($id){
        $error_log = "tmp/error" . $id . ".txt";
        $this->descriptorSpec[2] = array("file", $error_log, "w");

    }

    /**
     *  Открываем новый процесс
     * @param $url
     * @param $accID
     */
    public function openProc($url, $accID)
    {
        $urls = implode(',', $url);
        $id = ++$this->id;

        $this->setErrorLog($id);

        $cmd = 'php yii parse/page \'' . $urls . '\'  \'' . $accID . '\']';

        $this->handles[$id] = proc_open($cmd, $this->descriptorSpec, $pipes);

        $this->streams[$id] = $pipes[1];
        $this->all_pipes[$id] = $pipes;

    }

    /**
     * Получаем результаты, записанные из (уже теперь) закрытых процессов
     * и обнуляем массив результатов(освобождаем память).
     * @return array
     */
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


    /**
     * Проверяем можно ли создать новый поток -
     * Если количество существующих потоков меньше,
     * чем Максимальное кол-во, то возвращаем true,
     * иначе false
     * @return bool
     */
    public function allowNewStream()
    {
        $currentNum = count($this->streams);
        $maxNum = $this->maxStreams;
        if (!($currentNum > $maxNum)) {
            return true;
        }
        return false;
    }

    /**
     * В массиве потоков находим айди каждого потока,
     * если поток прекратил выполнение, то записываем
     * вывод этого потока в $this->result и закрываем
     * его.
     *
     * @param $read
     */
    private function eventProcess($read)
    {
        foreach ($read as $r) {
            $id = array_search($r, $this->streams);
                $this->result[] = stream_get_contents($this->all_pipes[$id][1]);
            if (feof($r)) {
                fclose($this->all_pipes[$id][0]);
                fclose($this->all_pipes[$id][1]);
                proc_close($this->handles[$id]);
                unset($this->handles[$id]);
                unset($this->streams[$id]);
                echo "\n\r" . $id . ' done';
            }
        }
    }

    /**
     * Начинаем прослушку потоков
     */
    private function streamCheck()
    {
        $read = $this->streams;
        $w = null;
        $e = null;
        stream_select($read, $w, $e, $this->timeout);
        $this->eventProcess($read);
    }

    /**
     *
     */
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
