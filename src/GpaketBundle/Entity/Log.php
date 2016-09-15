<?php

namespace GpaketBundle\Entity;

/**
 * Log
 */
class Log
{
    /**
     * @var int
     */
    private $id;

    /**
     * @var string
     */
    private $data;

    /**
     * @var \DateTime
     */
    private $date;


    /**
     * Get id
     *
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set data
     *
     * @param string $data
     *
     * @return Log
     */
    public function setData($data)
    {
        $this->data = $data;

        return $this;
    }

    /**
     * Get data
     *
     * @return string
     */
    public function getData()
    {
    	$data = json_decode($this->data, true);
	    $user = '';
	    $msg = '';
	    $time = '';
	    if (isset($data['message']))
		    $time = date('d m Y H:m:i', $data['message']['date']);
	    if (isset($data['message']['from']['username']))
	        $user = $data['message']['from']['username'];
	    if (isset($data['message']['text']))
	        $msg = $data['message']['text'];
        return "[$time] {$user}: $msg";
    }

    /**
     * Set date
     *
     * @param \DateTime $date
     *
     * @return Log
     */
    public function setDate($date)
    {
        $this->date = $date;

        return $this;
    }

    /**
     * Get date
     *
     * @return \DateTime
     */
    public function getDate()
    {
        return $this->date;
    }
}
