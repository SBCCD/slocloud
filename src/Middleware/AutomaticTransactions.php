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
namespace SLOCloud\Middleware;

use Doctrine\Common\EventSubscriber;
use Doctrine\ORM\Events;
use Slim\Middleware;

/**
 * This middleware will automatically start, commit, and rollback transactions based on doctrine events.
 * If you need transaction support for any non-doctrine changes, be sure to dispatch the pre & post
 * non-doctrine events before and after the change.
 * @package SLOCloud\Middleware
 */
class AutomaticTransactions extends Middleware implements EventSubscriber
{
    /** @var \SLOCloud\Application */
    protected $app;
    private $changesMade = false;
    private $transactionStarted = false;

    /* Events to post before/after any changes needing a commit that doctrine does not handle */
    const POST_NON_DOCTRINE_EVENT_NEEDING_COMMIT = "postNonDoctrineEventNeedingCommit";
    const PRE_NON_DOCTRINE_EVENT_NEEDING_COMMIT = "preNonDoctrineEventNeedingCommit";

    public function call()
    {
        $app = $this->app;
        $log = $app->log;
        $app->dbEvm->addEventSubscriber($this);

        if (!$app->container->has('db')) {
            $log->info("No db found. AutomaticTransactions disabled");
            $this->next->call();
            return;
        }

        $db = $app->db;
        try {
            $this->next->call();
            if ($this->transactionStarted && $this->changesMade) {
                if ($db->commit() === false) {
                    $log->error("Failed to commit transaction");
                }
            }
        } catch (\Exception $e) {
            if ($this->transactionStarted) {
                if ($db->rollback() === false) {
                    $log->error("Failed to rollback transaction");
                }
            }
            throw $e;
        }
    }

    private function beginTransaction($message)
    {
        if (!$this->transactionStarted) {
            $this->app->db->beginTransaction();
            $this->transactionStarted = true;
            $this->app->log->debug("Begin Transaction: $message");
        }
    }

    private function signalChanges($message)
    {
        if (!$this->changesMade) {
            $this->changesMade = true;
            $this->app->log->debug("Signal Changes: $message");
        }
    }

    /**
     * @inheritdoc
     */
    public function getSubscribedEvents()
    {
        // We want to listen to any events that result in database changes, so we
        // know when a commit is needed
        return [
            Events::preUpdate,
            Events::postUpdate,
            Events::preRemove,
            Events::postRemove,
            Events::prePersist,
            Events::postPersist,
            AutomaticTransactions::PRE_NON_DOCTRINE_EVENT_NEEDING_COMMIT,
            AutomaticTransactions::POST_NON_DOCTRINE_EVENT_NEEDING_COMMIT
        ];
    }

    public function preUpdate()
    {
        $this->beginTransaction("Entity will be updated");
    }

    public function postUpdate()
    {
        $this->signalChanges("Entity updated");
    }

    public function preRemove()
    {
        $this->beginTransaction("Entity will be removed");
    }

    public function postRemove()
    {
        $this->signalChanges("Entity removed");
    }

    public function prePersist()
    {
        $this->beginTransaction("Entity will be added");
    }

    public function postPersist()
    {
        $this->signalChanges("Entity added");
    }

    public function preNonDoctrineEventNeedingCommit()
    {
        $this->beginTransaction("Non-doctrine change being made");
    }

    public function postNonDoctrineEventNeedingCommit()
    {
        $this->signalChanges("Non-doctrine change made");
    }
}
