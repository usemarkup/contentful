<?php

namespace Markup\Contentful;

/**
 * Marker interface for when a parameter cannot be used directly, but must be transformed before use.
 * The getKey() method on the parameter should throw a LogicException.
 */
interface IncompleteParameterInterface extends ParameterInterface
{}
