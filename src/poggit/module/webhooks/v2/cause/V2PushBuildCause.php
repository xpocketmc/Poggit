<?php

/*
 * Poggit
 *
 * Copyright (C) 2016 Poggit
 *
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 *     http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 */

namespace poggit\module\webhooks\v2\cause;

use poggit\Poggit;
use poggit\session\SessionUtils;

class V2PushBuildCause extends V2BuildCause {
    /** @var int */
    public $repoId;
    /** @var string */
    public $commit;

    public function echoHtml() {
        $token = SessionUtils::getInstance()->getAccessToken();
        $repo = Poggit::ghApiGet("repositories/$this->repoId", $token);
        $commit = Poggit::ghApiGet("repositories/$this->repoId/commits/$this->commit", $token);
        ?>
        <p>Triggered by commit
            <code class="code"><?= substr($this->commit, 0, 7) ?></code> <?php Poggit::ghLink($commit->html_url) ?>
            by <img src="<?= $commit->author->avatar_url ?>">
            <?= $commit->author->login ?><?php Poggit::ghLink($commit->author->html_url) ?>
            <?php if($commit->author->login !== $commit->committer->login) { ?>
                with <img src="<?= $commit->committer->avatar_url ?>">
                <?= $commit->committer->login ?><?php Poggit::ghLink($commit->committer->html_url) ?>
            <?php } ?>
            in <?= $repo->owner->login ?><?php Poggit::ghLink($repo->owner->html_url) ?>
            / <?= $repo->name ?><?php Poggit::ghLink($repo->html_url) ?>:
        </p>
        <pre class="code"><?= $commit->commit->message ?></pre>
        <?php
    }
}
