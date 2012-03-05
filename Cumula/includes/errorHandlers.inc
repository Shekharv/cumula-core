<?php
/*

register_shutdown_function('handle_shutdown');
set_exception_handler('handle_exception');
set_error_handler('handle_error');

function handle_exception($e) {
	\Cumula\Error::handleException($e);
}

function handle_error($code, $message, $filepath, $line) {
	\Cumula\Error::handleError($code, $message, $filepath, $line);
}

function handle_shutdown() {
	\Cumula\Error::handleShutdown();
}
* */