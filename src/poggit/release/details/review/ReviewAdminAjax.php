<?php

/*
 * Poggit
 *
 * Copyright (C) 2016-2018 Poggit
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

namespace poggit\release\details\review;

use poggit\account\Session;
use poggit\Config;
use poggit\Meta;
use poggit\module\AjaxModule;
use poggit\utils\internet\Discord;
use poggit\utils\internet\GitHub;
use poggit\utils\internet\Mysql;
use RuntimeException;
use function strlen;

class ReviewAdminAjax extends AjaxModule {
    protected function impl() {
        // read post fields
        $action = $this->param("action");
        $relId = (int) $this->param("relId");

        $session = Session::getInstance();
        $user = $session->getName();
        $userLevel = Meta::getAdmlv($user);
        $userUid = $session->getUid();
        $repoIdRows = Mysql::query("SELECT releases.name, releases.version, repoId FROM releases
                INNER JOIN projects ON releases.projectId = projects.projectId
                WHERE releaseId = ? LIMIT 1",
            "i", $relId);
        if(!isset($repoIdRows[0])) {
            $this->errorBadRequest("Nonexistent releaseId");
        }
        $repoId = (int) $repoIdRows[0]["repoId"];

        switch($action) {
            case "add":
                $score = (int) $this->param("score");
                if(!(0 <= $score && $score <= 5)) {
                    $this->errorBadRequest("0 <= score <= 5");
                }
                $message = $this->param("message");
                if(strlen($message) > Config::MAX_REVIEW_LENGTH && $userLevel < Meta::ADMLV_MODERATOR) {
                    $this->errorBadRequest("Message too long");
                }
                if(GitHub::testPermission($repoId, $session->getAccessToken(), $session->getName(), "push")) {
                    $this->errorBadRequest("You can't review your own release");
                }
                try {
                    Mysql::query("INSERT INTO release_reviews (releaseId, user, criteria, type, cat, score, message) VALUES (?, ? ,? ,? ,? ,? ,?)",
                        "iiiiiis", $relId, $userUid, $_POST["criteria"] ?? PluginReview::DEFAULT_CRITERIA, (int) $this->param("type"),
                        (int) $this->param("category"), $score, $message); // TODO support GFM
                } catch(RuntimeException $e) {
                    $this->errorBadRequest("Duplicate review");
                }

                if(!Meta::isDebug()) {
                    $ip = Meta::getClientIP();
                    Discord::auditHook("{$session->getName()} ($ip) reviewed https://poggit.pmmp.io/p/{$repoIdRows[0]["name"]}/{$repoIdRows[0]["version"]} ($score/5):\n\n```\n$message\n```", "User reviews");
                }

                break;
            case "delete" :
                $author = $this->param("author");
                $authorUid = PluginReview::getUIDFromName($author) ?? 0;
                if(($userLevel >= Meta::ADMLV_MODERATOR) || ($userUid === $authorUid)) { // Moderators up
                    Mysql::query("DELETE FROM release_reviews WHERE (releaseId = ? AND user = ? AND criteria = ?)",
                        "iii", $relId, $authorUid, $_POST["criteria"] ?? PluginReview::DEFAULT_CRITERIA);
                }
                break;
        }
    }
}
