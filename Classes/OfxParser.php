<?php

namespace Grimfor\OfxParserBundle\Classes;

/**
 * An OFX parser library.
 * 
 * @author Guillaume BAILLEUL <contact@guillaume-bailleul.fr>
 */
class OfxParser {

    private $OFXContent;
    private $originalString;
    private $moviments;
    private $conf;
    private $db; // use this to add a layer to your database

    private function fixTag($tag, $acceptSpaces = false) {
        $tag = strtoupper($tag);
        $this->OFXContent = preg_replace('/<' . $tag . '>([\w0-9\.\-_\+\,' 
                . (($acceptSpaces) ? ' ' : '') 
                . '])+/i', '$0</' . $tag . '>', $this->OFXContent);
    }

    public function loadFromFile($OFXFile) {
        if (file_exists($OFXFile)){
            $this->loadFromString(file_get_contents($OFXFile));
        }
        else{
            return false;
        }
    }

    public function loadFromString($OFXContent) {
        $this->originalString = $OFXContent;
        $this->OFXContent = $OFXContent;

        $this->OFXContent = explode('<OFX>', $this->OFXContent);
        $this->conf = $this->OFXContent[0];
        $this->OFXContent = '<OFX>' . $this->OFXContent[1];
        $this->fixTag('BRANCHID');
        $this->fixTag('DTUSER');
        $this->fixTag('NAME', true);
        $this->fixTag('code');
        $this->fixTag('SEVERITY');
        $this->fixTag('CURDEF');
        $this->fixTag('DTSERVER');
        $this->fixTag('LANGUAGE');
        $this->fixTag('TRNUID');
        $this->fixTag('BANKID');
        $this->fixTag('ACCTID');
        $this->fixTag('ACCTTYPE');
        $this->fixTag('DTSTART');
        $this->fixTag('DTEND');
        $this->fixTag('TRNTYPE');
        $this->fixTag('DTPOSTED');
        $this->fixTag('TRNAMT');
        $this->fixTag('FITID');
        $this->fixTag('CHECKNUM');
        $this->fixTag('DTASOF');
        $this->fixTag('LEDGERBAL');
        $this->fixTag('BALAMT');
        $this->fixTag('LEDGERBAL');
        $this->fixTag('MEMO', true);
        
        $xmlContent = "<?xml version='1.0'?> " . $this->OFXContent;

        $this->ofx = simplexml_load_string($xmlContent);

        $conf = explode('/r', trim($this->conf));
        $this->conf = Array();

        for ($i = 0, $j = sizeof($conf); $i < $j; $i++) {
            $conf[$i] = explode(':', $conf[$i], 2);
            $this->conf[$conf[$i][0]] = $conf[$i][1];
        }
        $this->moviments = Array();

        foreach ($this->ofx->BANKMSGSRSV1->STMTTRNRS->STMTRS->BANKTRANLIST->STMTTRN as $mov) {
            $this->moviments[] = (array) $mov;
        }
        unset($this->originalString);
        unset($this->ofx);
    }

    public function OFXParser($OFXContent = false) {
        if ($OFXContent)
            $this->loadFromString($OFXContent);
    }

    /*     * ************************************************************* */
    /*     * **             external functions to be used              *** */
    /*     * **            (advised to be used for filters)            *** */
    /*     * ************************************************************* */

    public function filter($what, $forWhat, $case = true, $like = false) {
        $case = !$case;
        $ret = Array();
        if ($case) {
            $what = strtoupper($what);
            $forWhat = strtoupper($forWhat);
        }
        for ($i = 0, $j = sizeof($this->moviments); $i < $j; $i++) {
            if ($like) {
                if (strstr($this->moviments[$i][$what], $forWhat))
                    $ret[] = $this->moviments[$i];
            }else {
                if ($this->moviments[$i][$what] == $forWhat)
                    $ret[] = $this->moviments[$i];
            }
        }
        return $ret;
    }

    public function getMoviments() {
        return $this->moviments;
    }

    public function getMemoLike($memo) {
        return $this->filter('MEMO', $memo, true, false);
    }

    public function getById($id) {
        return $this->filter('FITID', $id);
    }

    public function getByCheckNum($id) {
        return $this->filter('CHECKNUM', $id);
    }

    public function getCredits($min = 0) {
        $ret = Array();
        for ($i = 0, $j = sizeof($this->moviments); $i < $j; $i++) {
            if ($this->moviments[$i]['TRNTYPE'] == 'CREDIT' && $this->moviments[$i]['TRNAMT'] >= $min)
                $ret[] = $this->moviments[$i];
        }
        return $ret;
    }

    public function getByDate($month, $day, $year) {
        if ($month < 10)
            $month = '0' . $month;
        if ($day < 10)
            $day = '0' . $day;
        if ($year < 100)
            if ($year < 10)
                $year = '00' . $year;
            else
                $year = '0' . $year;
        $date = '/^' . $year . $month . $day . '([0-9])+$/';

        $ret = Array();
        for ($i = 0, $j = sizeof($this->moviments); $i < $j; $i++) {
            if (preg_match($date, $this->moviments[$i]['DTPOSTED'])) {
                $ret[] = $this->moviments[$i];
            }
        }
        return $ret;
    }

    public function getMoviment($mov) {
        if (isset($this->moviments[$mov]))
            return $this->moviments[$mov];
        else
            return false;
    }

    public function getDebits($min = 0) {
        $ret = Array();

        if ($min > 0)
            $min*= (-1);

        for ($i = 0, $j = sizeof($this->moviments); $i < $j; $i++) {
            if ($this->moviments[$i]['TRNTYPE'] == 'DEBIT' && $this->moviments[$i]['TRNAMT'] <= $min)
                $ret[] = $this->moviments[$i];
        }
        return $ret;
    }

}

?>