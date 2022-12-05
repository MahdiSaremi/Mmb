<?php

namespace Mmb; #auto

class MmbBase
{

    /**
     * اجرای اسلیپ و برگرداندن خود
     *
     * @param float $seconds
     * @return $this
     */
    public function sleep($seconds){
        sleep($seconds);
        return $this;
    }

}
