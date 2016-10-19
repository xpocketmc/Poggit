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

namespace poggit\builder\lint;

class MainClassMissingLint extends BuildLint {
    public $level = BuildResult::LEVEL_ERROR;

    /** @var string */
    public $expectedFile;

    public function echoHtml() {
        ?>
        <p>Expected main class file at <code class="code"><?= htmlspecialchars($this->expectedFile) ?></code> missing
        </p>
        <?php
    }
}
