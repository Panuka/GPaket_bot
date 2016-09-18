<?php

namespace GpaketBundle\Entity;

/**
 * Dictionary
 */
class Dictionary
{
    /**
     * @var integer
     */
    private $id;

    /**
     * @var string
     */
    private $keyword;

    /**
     * @var array
     */
    private $answers;


    /**
     * Get id
     *
     * @return integer
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set keyword
     *
     * @param string $keyword
     *
     * @return Dictionary
     */
    public function setKeyword($keyword)
    {
        $this->keyword = $keyword;

        return $this;
    }

    /**
     * Get keyword
     *
     * @return string
     */
    public function getKeyword()
    {
        return $this->keyword;
    }

    /**
     * Set answers
     *
     * @param array $answers
     *
     * @return Dictionary
     */
    public function setAnswers($answers)
    {
        $this->answers = $answers;

        return $this;
    }

    /**
     * Get answers
     *
     * @return array
     */
    public function getAnswers()
    {
        return $this->answers;
    }
}

