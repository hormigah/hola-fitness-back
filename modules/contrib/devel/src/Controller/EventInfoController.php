<?php

namespace Drupal\devel\Controller;

use Drupal\Core\Controller\ControllerBase;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

/**
 * Provides route responses for the event info page.
 */
class EventInfoController extends ControllerBase {

  /**
   * Event dispatcher service.
   *
   * @var \Symfony\Component\EventDispatcher\EventDispatcherInterface
   */
  protected $eventDispatcher;

  /**
   * EventInfoController constructor.
   *
   * @param \Symfony\Component\EventDispatcher\EventDispatcherInterface $event_dispatcher
   *   Event dispatcher service.
   */
  public function __construct(EventDispatcherInterface $event_dispatcher) {
    $this->eventDispatcher = $event_dispatcher;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('event_dispatcher')
    );
  }

  /**
   * Builds the events overview page.
   *
   * @return array
   *   A render array as expected by the renderer.
   */
  public function eventList() {
    $headers = [
      'name' => [
        'data' => $this->t('Event Name'),
        'class' => 'visually-hidden',
      ],
      'callable' => $this->t('Callable'),
      'priority' => $this->t('Priority'),
    ];

    $event_listeners = $this->eventDispatcher->getListeners();
    ksort($event_listeners);

    $rows = [];

    foreach ($event_listeners as $event_name => $listeners) {

      $rows[][] = [
        'data' => $event_name,
        'class' => ['devel-event-name-header'],
        'filter' => TRUE,
        'colspan' => '3',
        'header' => TRUE,
      ];

      foreach ($listeners as $priority => $listener) {
        $row['name'] = [
          'data' => $event_name,
          'class' => ['visually-hidden'],
          'filter' => TRUE,
        ];
        $row['class'] = [
          'data' => $this->resolveCallableName($listener),
        ];
        $row['priority'] = [
          'data' => $priority,
        ];
        $rows[] = $row;
      }
    }

    $output['events'] = [
      '#type' => 'devel_table_filter',
      '#filter_label' => $this->t('Search'),
      '#filter_placeholder' => $this->t('Enter event name'),
      '#filter_description' => $this->t('Enter a part of the event name to filter by.'),
      '#header' => $headers,
      '#rows' => $rows,
      '#empty' => $this->t('No events found.'),
      '#attributes' => [
        'class' => ['devel-event-list'],
      ],
    ];

    return $output;
  }

  /**
   * Helper function for resolve callable name.
   *
   * @param mixed $callable
   *   The for which resolve the name. Can be either the name of a function
   *   stored in a string variable, or an object and the name of a method
   *   within the object.
   *
   * @return string
   *   The resolved callable name or an empty string.
   */
  protected function resolveCallableName($callable) {
    if (is_callable($callable, TRUE, $callable_name)) {
      return $callable_name;
    }
    return '';
  }

}
