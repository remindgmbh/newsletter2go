<?php

declare(strict_types=1);

namespace Remind\Newsletter2go\Domain\Finishers;

use TYPO3\CMS\Form\Domain\Finishers\AbstractFinisher;

class Newsletter2goSubscribeFinisher extends AbstractFinisher
{
    protected function executeInternal(): ?string
    {
        /* \TYPO3\CMS\Extbase\Utility\DebuggerUtility::var_dump($this->finisherContext);
        new testCall(); */
    }
}
