<?php
#auto-name
namespace Mmb\IO;

trait BinaryIOTrait
{

    public function writeInt16($integer)
    {
        $this->write(pack('s', $integer));
    }
    public function readInt16()
    {
        return unpack('s', $this->read(2))[1];
    }
    
    public function writeInt32($integer)
    {
        $this->write(pack('l', $integer));
    }
    public function readInt32()
    {
        return unpack('l', $this->read(4))[1];
    }
    
    public function writeInt64($integer)
    {
        $this->write(pack('q', $integer));
    }
    public function readInt64()
    {
        return unpack('q', $this->read(8))[1];
    }

    public static function getSystemDoubleSize()
    {
        static $size = null;
        if($size === null)
        {
            $size = strlen(pack('d', 0.0));
        }
        return $size;
    }

    public function writeDouble64($double)
    {
        $bit = pack('d', $double);
        if(strlen($bit) < 8)
            $bit = str_repeat(chr(0), 8 - strlen($bit)) . $bit;
        $this->write($bit);
    }
    public function readDouble64()
    {
        $bit = $this->read(8);
        if (self::getSystemDoubleSize() < 8)
            $bit = substr($bit, 8 - self::getSystemDoubleSize());
        return unpack('d', $bit)[1];
    }

    public function writeByte($byte)
    {
        $this->write(chr($byte));
    }
    public function readByte()
    {
        return ord($this->read());
    }

    public function writeBytes($bytes, $length = null, $offset = 0)
    {
        if ($length === null)
            $length = count($bytes) - $offset;

        for ($i = 0; $i < $length; $i++) {
            $this->write($bytes[$offset + $i]);
        }
    }
    public function readBytes($length)
    {
        $res = [];
        for ($i = 0; ($i < $length) && ($char = $this->read()) !== false; $i++) {
            $res[] = ord($char);
        }
        return $res;
    }


    // Read & Write with auto length
    public function writeIntL($integer)
    {
        $abs = abs($integer);
        if($abs < 120)
        {
            $this->writeByte(1);
            $this->writeByte($integer + 120);
        }
        elseif($abs < 32000)
        {
            $this->writeByte(2);
            $this->writeInt16($integer);
        }
        elseif($abs < 2000000000)
        {
            $this->writeByte(3);
            $this->writeInt32($integer);
        }
        else
        {
            $this->writeByte(4);
            $this->writeInt64($integer);
        }
    }
    public function readIntL()
    {
        $type = $this->readByte();
        if($type == 1)
        {
            return $this->readByte() - 120;
        }
        elseif($type == 2)
        {
            return $this->readInt16();
        }
        elseif($type == 3)
        {
            return $this->readInt32();
        }
        elseif($type == 4)
        {
            return $this->readInt64();
        }
        return false;
    }

    public function writeStringL($string)
    {
        $this->writeIntL(strlen($string));
        $this->write($string);
    }
    public function readStringL()
    {
        $length = $this->readIntL();
        return $this->read($length);
    }

}
