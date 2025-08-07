<?php

namespace Vektor\Shop;

use Illuminate\Contracts\Events\Dispatcher;
use Illuminate\Session\SessionManager;
use Illuminate\Support\Collection;

class Customisations
{
    public const DEFAULT_INSTANCE = 'default';

    /**
     * Instance of the session manager.
     *
     * @var SessionManager
     */
    private $session;

    /**
     * Instance of the event dispatcher.
     *
     * @var Dispatcher
     */
    private $events;

    /**
     * Holds the current customisation instance.
     *
     * @var string
     */
    private $instance;

    /**
     * Customisation constructor.
     */
    public function __construct(SessionManager $session, Dispatcher $events)
    {
        $this->session = $session;
        $this->events = $events;

        $this->instance(self::DEFAULT_INSTANCE);
    }

    /**
     * Set the current customisation instance.
     *
     * @param null|string $instance
     *
     * @return Customisations
     */
    public function instance($instance = null)
    {
        $instance = $instance ?: self::DEFAULT_INSTANCE;

        $this->instance = 'customisations.'.$instance;

        return $this;
    }

    /**
     * Get the current customisation instance.
     *
     * @return string
     */
    public function currentInstance()
    {
        return str_replace('customisations.', '', $this->instance);
    }

    /**
     * @param int   $id
     * @param array $options
     *
     * @return array
     */
    public function add($id, $options)
    {
        $customisation = $this->createCustomisation($id, $options);

        return $this->addCustomisation($customisation);
    }

    public function update($id, $options)
    {
        $customisation = $this->get($id);
        $customisation = $this->setDesigns($customisation, $options['designs']);

        $customisation['note'] = $options['note'];

        $content = $this->customisations();

        $content->put($id, $customisation);

        $this->session->put($this->instance, $content);

        return $customisation;
    }

    public function remove($id)
    {
        $customisation = $this->get($id);

        $content = $this->customisations();

        $content->pull($customisation['id']);

        $this->session->put($this->instance, $content);

        return true;
    }

    public function get($id)
    {
        $content = $this->customisations();

        if (!$content->has($id)) {
            throw new \Exception('Customisation does not exist');
        }

        return $content->get($id);
    }

    public function has($id)
    {
        $content = $this->customisations();

        return $content->has($id);
    }

    public function destroy()
    {
        $this->session->remove($this->instance);
    }

    /**
     * Get only customisations connected to cart items.
     *
     * @param mixed $previews
     *
     * @return Collection
     */
    public function cart_customisations($previews = true)
    {
        $customisations = $previews ? $this->customisations() : $this->customisations_np();

        return $customisations->whereIn('id', Cart::content()->pluck('id'));
    }

    /**
     * Get customisations without base64 encoded previews.
     *
     * @return Collection
     */
    public function customisations_np()
    {
        return $this->customisations()->map(function ($c) {
            foreach ($c['designs'] as &$design) {
                unset($design['preview']);
            }

            return $c;
        });
    }

    /**
     * Get customisations, if there are none set yet, return a new empty Collection.
     *
     * @return Collection
     */
    public function customisations()
    {
        if (is_null($this->session->get($this->instance))) {
            return new Collection([]);
        }

        return $this->session->get($this->instance);
    }

    public function count()
    {
        return $this->customisations()->count();
    }

    /**
     * @param int   $id
     * @param array $options
     *
     * @return array
     */
    private function createCustomisation($id, $options)
    {
        $customisation = [
            'id' => $id,
            'designs' => [],
            'note' => $options['note'],
        ];

        return $this->setDesigns($customisation, $options['designs']);
    }

    /**
     * @param array $options
     * @param mixed $customisation
     *
     * @return array
     */
    private function setDesigns($customisation, $options)
    {
        foreach ($options as $option => $details) {
            if (isset($details['selected']) && $details['selected']) {
                $customisation['designs'][$option] = $details;
            }
        }

        return $customisation;
    }

    /**
     * @param array $customisation
     *
     * @return array
     */
    private function addCustomisation($customisation)
    {
        $content = $this->customisations();

        if ($content->has($customisation['id'])) {
            return false;
        }

        $content->put($customisation['id'], $customisation);

        $this->session->put($this->instance, $content);

        return $customisation;
    }
}
