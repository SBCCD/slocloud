<?php /*

SLO Cloud - A Cloud-Based SLO Reporting Tool for Higher Education

This is a peer-reviewed, open-source, public project made possible by the Open Innovation in Higher Education project.

Copyright (C) 2015 Jesse Lawson, San Bernardino Community College District

Contributors:
Jesse Lawson
Jason Brady

THIS PROJECT IS LICENSED UNDER GPLv2. YOU MAY COPY, DISTRIBUTE AND MODIFY THE SOFTWARE AS LONG AS YOU TRACK
CHANGES/DATES OF IN SOURCE FILES AND KEEP ALL MODIFICATIONS UNDER GPL. YOU CAN DISTRIBUTE YOUR APPLICATION USING A
GPL LIBRARY COMMERCIALLY, BUT YOU MUST ALSO DISCLOSE THE SOURCE CODE.

GNU General Public License Version 2 Disclaimer:

---

This file is part of SLO Cloud

SLO Cloud is free software; you can redistribute it and/or modify it under the terms of the GNU General Public
License as published by the Free Software Foundation; either version 2 of the License, or (at your option) any later.

SLO Cloud is distributed in the hope that it will be useful, but WITHOUT ANY WARRANTY; without even the implied
warranty of MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the GNU General Public License for more details.

You should have received a copy of the GNU General Public License along with this program; if not, write to the Free
Software Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA 02111-1307 USA or
visit http://opensource.org/licenses/GPL-2.0

---

*/
namespace SLOCloud\Model\Storage;

use Doctrine\ORM\Mapping as ORM;

abstract class Statement implements \JsonSerializable
{
    /** @var integer */
    private $id;

    /** @var string */
    private $statement;

    /** @var integer */
    private $rubric1;

    /** @var integer */
    private $rubric2;

    /** @var integer */
    private $rubric3;

    /** @var integer */
    private $rubric4;

    /** @var string */
    private $met;

    /** @var string */
    private $plo;

    /** @var string */
    private $geo;

    /** @var string */
    private $ilo;

    /** @var SLO */
    private $slo;

    /**
     * @param string $statement
     * @param int $rubric1
     * @param int $rubric2
     * @param int $rubric3
     * @param int $rubric4
     * @param string $met
     * @param string $plo
     * @param string $geo
     * @param string $ilo
     * @param null|SLO $slo
     * @param null|int $id
     */
    public function __construct(
        $statement,
        $rubric1,
        $rubric2,
        $rubric3,
        $rubric4,
        $met,
        $plo,
        $geo,
        $ilo,
        $slo = null,
        $id = null
    ) {
        if ($id !== null) {
            $this->id = $id;
        }
        if ($slo !== null) {
            $this->setSlo($slo);
        }
        $this->setStatement($statement);
        $this->setRubric1($rubric1);
        $this->setRubric2($rubric2);
        $this->setRubric3($rubric3);
        $this->setRubric4($rubric4);
        $this->setMet($met);
        $this->setPlo($plo);
        $this->setGeo($geo);
        $this->setIlo($ilo);
    }

    public function getTotalAssessed()
    {
        return $this->getRubric1() + $this->getRubric2() + $this->getRubric3() + $this->getRubric4();
    }

    public function getTotalSufficient()
    {
        return $this->getRubric3() + $this->getRubric4();
    }

    public function getSufficient()
    {
        return number_format(($this->getTotalSufficient()/$this->getTotalAssessed())*100, 1);
    }

    /** @return integer */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @param string $statement
     * @return Statement
     */
    public function setStatement($statement)
    {
        $this->statement = $statement;

        return $this;
    }

    /** @return string */
    public function getStatement()
    {
        return $this->statement;
    }

    /**
     * @param integer $rubric1
     * @return Statement
     */
    public function setRubric1($rubric1)
    {
        $this->rubric1 = $rubric1;

        return $this;
    }

    /** @return integer */
    public function getRubric1()
    {
        return $this->rubric1;
    }

    /**
     * @param integer $rubric2
     * @return Statement
     */
    public function setRubric2($rubric2)
    {
        $this->rubric2 = $rubric2;

        return $this;
    }

    /** @return integer */
    public function getRubric2()
    {
        return $this->rubric2;
    }

    /**
     * @param integer $rubric3
     * @return Statement
     */
    public function setRubric3($rubric3)
    {
        $this->rubric3 = $rubric3;

        return $this;
    }

    /** @return integer */
    public function getRubric3()
    {
        return $this->rubric3;
    }

    /**
     * @param integer $rubric4
     * @return Statement
     */
    public function setRubric4($rubric4)
    {
        $this->rubric4 = $rubric4;

        return $this;
    }

    /** @return integer */
    public function getRubric4()
    {
        return $this->rubric4;
    }

    /**
     * @param string $met
     * @return Statement
     */
    public function setMet($met)
    {
        $this->met = $met;

        return $this;
    }

    /** @return string */
    public function getMet()
    {
        return $this->met;
    }

    /**
     * @param string $plo
     * @return Statement
     */
    public function setPlo($plo)
    {
        $this->plo = $plo;

        return $this;
    }

    /** @return string */
    public function getPlo()
    {
        return $this->plo;
    }

    /**
     * @param string $geo
     * @return Statement
     */
    public function setGeo($geo)
    {
        $this->geo = $geo;

        return $this;
    }

    /** @return string */
    public function getGeo()
    {
        return $this->geo;
    }

    /**
     * @param string $ilo
     * @return Statement
     */
    public function setIlo($ilo)
    {
        $this->ilo = $ilo;

        return $this;
    }

    /** @return string */
    public function getIlo()
    {
        return $this->ilo;
    }

    /**
     * @param SLO $slo
     * @return Statement
     */
    public function setSlo(SLO $slo)
    {
        $this->slo = $slo;

        return $this;
    }

    /** @return SLO */
    public function getSlo()
    {
        return $this->slo;
    }
}
