<?php namespace Cartalyst\Alerts;
/**
 * Part of the Alerts package.
 *
 * NOTICE OF LICENSE
 *
 * Licensed under the Cartalyst PSL License.
 *
 * This source file is subject to the Cartalyst PSL License that is
 * bundled with this package in the license.txt file.
 *
 * @package    Alerts
 * @version    0.1.0
 * @author     Cartalyst LLC
 * @license    Cartalyst PSL
 * @copyright  (c) 2011-2014, Cartalyst LLC
 * @link       http://cartalyst.com
 */

class Alerts {

	/**
	 * Notifiers.
	 *
	 * @var array
	 */
	protected $notifiers = [];

	/**
	 * Adds the given notifier.
	 *
	 * @param  string  $type
	 * @param  \Cartalyst\Alerts\NotifierInterface $notifier
	 * @return void
	 */
	public function addNotifier($type, NotifierInterface $notifier)
	{
		$this->notifiers[$type] = $notifier;
	}

	/**
	 * Removes the given type from notifiers.
	 *
	 * @param  string  $type
	 * @return void
	 */
	public function removeNotifier($type)
	{
		unset($this->notifiers[$type]);
	}

	/**
	 * Returns all or a sepcific type of alerts.
	 *
	 * @param  string  $type
	 * @return array
	 */
	public function all($type = null)
	{
		$messages = [];

		foreach ($this->notifiers as $notifier)
		{
			$messages = array_merge_recursive($messages, $notifier->all());
		}

		if ($type)
		{
			$messages = array_filter($messages, function($message) use ($type)
			{
				return $message->area === $type;
			});
		}

		return $messages;
	}

	/**
	 * Returns all except the given types of alerts.
	 *
	 * @param  array  $types
	 * @return array
	 */
	public function except($types = [])
	{
		if ( ! is_array($types))
		{
			$types = [$types];
		}

		$messages = [];

		foreach ($this->notifiers as $notifier)
		{
			$messages = array_merge_recursive($messages, $notifier->all());
		}

		foreach ($types as $type)
		{
			$messages = array_filter($messages, function($message) use ($type)
			{
				return $message->area !== $type;
			});
		}

		return $messages;
	}

	/**
	 * Returns form element errors.
	 *
	 * @param  string  $key
	 * @param  string  $alert
	 * @return string|null
	 */
	public function form($key, $alert = null)
	{
		$messages = $this->all('form') ?: [];

		foreach ($messages as $message)
		{
			if ($message->getKey() === $key)
			{
				return $alert ?: $message->message;
			}
		}
	}

	/**
	 * Returns the view notifier.
	 *
	 * @return \Cartalyst\Alerts\FlashNotifier
	 */
	public function view()
	{
		return $this->notifiers['view'];
	}

	/**
	 * Dynamically forward alerts.
	 *
	 * @param  string  $method
	 * @param  array  $parameters
	 * @return mixed
	 */
	public function __call($method, $parameters)
	{
		return call_user_func_array([$this->notifiers['default'], '__call'], [$method, $parameters]);
	}

}
