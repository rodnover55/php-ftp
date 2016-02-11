<?php
namespace Rnr\Ftp;

/**
 * @author Sergei Melnikov <me@rnr.name>
 */
class FileInfo
{
    private $name;
    private $date;
    private $type;

    const DIRECTORY = 'd';
    const FILE = 'f';

    /**
     * @return mixed
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @param mixed $name
     * @return FileInfo
     */
    public function setName($name)
    {
        $this->name = $name;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getDate()
    {
        return $this->date;
    }

    /**
     * @param mixed $date
     * @return FileInfo
     */
    public function setDate($date)
    {
        $this->date = $date;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * @param mixed $type
     * @return FileInfo
     */
    public function setType($type)
    {
        $this->type = $type;
        return $this;
    }


}