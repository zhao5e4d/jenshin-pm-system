<?php
css::import($this->app->getWebRoot() . 'js/zui3/jenshin.css?v=' . (@filemtime($this->app->getWwwRoot() . 'js/zui3/jenshin.css') ?: time()));
js::import($this->app->getWebRoot() . 'js/zui3/jenshin.js?v=' . (@filemtime($this->app->getWwwRoot() . 'js/zui3/jenshin.js') ?: time()));
