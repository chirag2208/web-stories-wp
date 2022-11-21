<?php

declare(strict_types = 1);

/*
 * Copyright 2022 Google LLC
 *
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 *     https://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 */

namespace Google\Web_Stories\Tests\Integration\Fixture;

use Google\Web_Stories\Post_Type_Base;

class DummyPostTypeWithoutArchive extends Post_Type_Base {
	public function get_slug(): string {
		return 'cpt-without-archive';
	}

	public function get_args(): array {
		return [
			'public'      => true,
			'rewrite'     => true,
			'has_archive' => false,
		];
	}
}
