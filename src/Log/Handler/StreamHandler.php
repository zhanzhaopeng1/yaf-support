<?php

namespace Yaf\Support\Log\Handler;

use Monolog\Handler\StreamHandler as BaseStreamHandler;
use Monolog\Logger;

class StreamHandler extends BaseStreamHandler
{
    const PLACEHOLDER_FILENAME = 'placeholder';
    const DEFAULT_DATE_FORMAT  = 'Y-m-d';

    /**
     * @var string $filename
     */
    protected $filename;

    /**
     * @var string $filenameFormat
     */
    protected $filenameFormat;

    /**
     * @var string $dateFormat
     */
    protected $dateFormat;

    /**
     * @var string $fileAbsolutePath
     */
    protected $fileAbsolutePath;

    /**
     * @var bool $hasFormat
     */
    protected $hasFormat = false;

    /**
     * @var bool $isCron
     */
    protected $isCron = false;

    /**
     * @var int $bufferLimit
     */
    protected $bufferLimit = -1;

    /**
     * @var int $writeFrequency 写日志的次数
     */
    protected $writeFrequency = 0;

    /**
     * StreamHandler constructor.
     * @param          $stream
     * @param bool     $isCron 是否是cron 脚本日志要每次都格式化文件
     * @param int      $bufferLimit
     * @param int      $level
     * @param bool     $bubble
     * @param int|null $filePermission
     * @param bool     $useLocking
     */
    public function __construct($stream, bool $isCron = false, int $bufferLimit = -1, $level = Logger::DEBUG, bool $bubble = true, ?int $filePermission = null, bool $useLocking = false)
    {
        $this->fileAbsolutePath = $stream;
        $this->isCron           = $isCron;
        $this->bufferLimit      = $bufferLimit;

        parent::__construct($stream, $level, $bubble, $filePermission, $useLocking);
    }

    protected function write(array $record): void
    {
        $this->writeFrequency += 1;

        /**
         * 脚本或者定时 要每次都格式化文件  防止都写到同一个文件中 导致文件过大
         */
        if (($this->isCron && $this->bufferLimit <= 0)
            || ($this->isCron && $this->bufferLimit > 0 && $this->writeFrequency == $this->bufferLimit)
            || !$this->hasFormat) {
            $fileInfo       = $this->parseLogFile($this->fileAbsolutePath);
            $this->filename = $fileInfo['file'];
            $this->setFilenameFormat($fileInfo['filenameFormat'], $fileInfo['dateFormat']);
            $this->url       = $this->getTimedFilename();
            $this->hasFormat = true;
        }

        parent::write($record);

        /**
         * 定时任务每写一次要关闭一次文件
         */
        if ($this->isCron) {
            if ($this->bufferLimit > 0 && $this->writeFrequency == $this->bufferLimit) {
                $this->close();
            } elseif ($this->bufferLimit <= 0) {
                $this->close();
            }
        }
    }

    /**
     * 设置文件格式
     *
     * @param $filenameFormat
     * @param $dateFormat
     */
    public function setFilenameFormat($filenameFormat, $dateFormat)
    {
        if (!preg_match('{^Y(([/_.-]?m)([/_.-]?d)([/_.-]?H)?)?$}', $dateFormat)) {
            trigger_error(
                'Invalid date format - format must be one of ' .
                'RotatingFileHandler::FILE_PER_DAY ("Y-m-d"), RotatingFileHandler::FILE_PER_MONTH ("Y-m") ' .
                'or RotatingFileHandler::FILE_PER_YEAR ("Y"), or you can set one of the ' .
                'date formats using slashes, underscores and/or dots instead of dashes.',
                E_USER_DEPRECATED
            );
        }
        if (substr_count($filenameFormat, '{date}') === 0) {
            trigger_error(
                'Invalid filename format - format should contain at least `{date}`, because otherwise rotating is impossible.',
                E_USER_DEPRECATED
            );
        }
        $this->filenameFormat = $filenameFormat;
        $this->dateFormat     = $dateFormat;
    }

    /**
     * 格式化文件名称加上时间
     *
     * @return mixed|string
     */
    protected function getTimedFilename()
    {
        $fileInfo = pathinfo($this->filename);

        $timedFilename = str_replace(
            array('{filename}', '{date}'),
            array($fileInfo['filename'], date($this->dateFormat)),
            $fileInfo['dirname'] . '/' . $this->filenameFormat
        );

        if (!empty($fileInfo['extension'])) {
            $timedFilename .= '.' . $fileInfo['extension'];
        }

        return $timedFilename;
    }

    /**
     * 解析日志文件名隐含信息(支持按日分隔定义 Ymd/YmdH.log)
     *
     * @param string $file
     * @return array
     */
    protected function parseLogFile(string $file): array
    {
        $info = [
            'file'  => $file,
            'daily' => false,
        ];

        // 支持 {date} 或 {date:Ymd}/{date:YmdH} 定义每日文件地址
        if (preg_match_all('/{date(?::([\w\d\-\.]+))?}/', $file, $match)) {
            $pathInfo = pathinfo($file);

            $timedFilename          = str_replace($match[0][0], date($match[1][0]), $pathInfo['dirname']);
            $info['file']           = $timedFilename . '/' . self::PLACEHOLDER_FILENAME;
            $info['daily']          = true;
            $info['filenameFormat'] = str_replace($match[0][1], '{date}', $pathInfo['basename']);
            $info['dateFormat']     = $match[1][1] ?? self::DEFAULT_DATE_FORMAT;
        }

        return $info;
    }
}