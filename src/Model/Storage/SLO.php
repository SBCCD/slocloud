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

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;

abstract class SLO implements \JsonSerializable
{
    /** @var integer */
    private $id;

    /** @var \DateTime */
    private $enteredOn;

    /** @var string */
    private $term;

    /** @var string */
    private $subject;

    /** @var string */
    private $class;

    /** @var string */
    private $section;

    /** @var string */
    private $method;

    /** @var string */
    private $proposed;

    /** @var string */
    private $plos;

    /** @var string */
    private $geos;

    /** @var string */
    private $ilos;

    /** @var Statement[] */
    private $statements;

    /**
     * Constructor
     * @param \DateTime $when
     * @param string $term
     * @param string $subject
     * @param string $class
     * @param string $section
     * @param string $method
     * @param string $proposed
     * @param string $plos
     * @param string $geos
     * @param string $ilos
     * @param Statement[] $statements
     * @param null:int $id
     */
    public function __construct(
        \DateTime $when,
        $term,
        $subject,
        $class,
        $section,
        $method,
        $proposed,
        $plos,
        $geos,
        $ilos,
        array $statements,
        $id = null
    ) {
        if ($id !== null) {
            $this->id = $id;
        }
        $this->setEnteredOn($when);
        $this->setTerm($term);
        $this->setSubject($subject);
        $this->setClass($class);
        $this->setSection($section);
        $this->setMethod($method);
        $this->setProposed($proposed);
        $this->setPlos($plos);
        $this->setGeos($geos);
        $this->setIlos($ilos);
        $this->statements = new ArrayCollection();
        foreach ($statements as $statement) {
            $this->addStatement($statement);
        }

    }

    /** @return integer */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @param \DateTime $enteredOn
     * @return SLO
     */
    public function setEnteredOn($enteredOn)
    {
        $this->enteredOn = $enteredOn;

        return $this;
    }

    /** @return \DateTime */
    public function getEnteredOn()
    {
        return $this->enteredOn;
    }

    /**
     * @param string $term
     * @return SLO
     */
    public function setTerm($term)
    {
        $this->term = $term;

        return $this;
    }

    /** @return string */
    public function getTerm()
    {
        return $this->term;
    }

    /**
     * @param string $subject
     * @return SLO
     */
    public function setSubject($subject)
    {
        $this->subject = $subject;

        return $this;
    }

    /** @return string */
    public function getSubject()
    {
        return $this->subject;
    }

    /**
     * @param string $class
     * @return SLO
     */
    public function setClass($class)
    {
        $this->class = $class;

        return $this;
    }

    /** @return string */
    public function getClass()
    {
        return $this->class;
    }

    /**
     * @param string $section
     * @return SLO
     */
    public function setSection($section)
    {
        $this->section = $section;

        return $this;
    }

    /** @return string */
    public function getSection()
    {
        return $this->section;
    }

    /**
     * @param string $method
     * @return SLO
     */
    public function setMethod($method)
    {
        $this->method = $method;

        return $this;
    }

    /** @return string */
    public function getMethod()
    {
        return $this->method;
    }

    /**
     * @param string $proposed
     * @return SLO
     */
    public function setProposed($proposed)
    {
        $this->proposed = $proposed;

        return $this;
    }

    /** @return string */
    public function getProposed()
    {
        return $this->proposed;
    }

    /**
     * @param string $plos
     * @return SLO
     */
    public function setPlos($plos)
    {
        $this->plos = $plos;

        return $this;
    }

    /** @return string */
    public function getPlos()
    {
        return $this->plos;
    }

    /**
     * @param string $geos
     * @return SLO
     */
    public function setGeos($geos)
    {
        $this->geos = $geos;

        return $this;
    }

    /** @return string */
    public function getGeos()
    {
        return $this->geos;
    }

    /**
     * @param string $ilos
     * @return SLO
     */
    public function setIlos($ilos)
    {
        $this->ilos = $ilos;

        return $this;
    }

    /** @return string */
    public function getIlos()
    {
        return $this->ilos;
    }

    /**
     * @param Statement $statement
     * @return SLO
     */
    public function addStatement(Statement $statement)
    {
        $this->statements[] = $statement;
        $statement->setSlo($this);

        return $this;
    }

    /** @param Statement $statement */
    public function removeStatement(Statement $statement)
    {
        $this->statements->removeElement($statement);
    }

    /** @return Statement[] */
    public function getStatements()
    {
        return $this->statements->toArray();
    }
}
