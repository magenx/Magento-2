<?php

/**
 * @see       https://github.com/laminas/laminas-server for the canonical source repository
 */

namespace Laminas\Server\Reflection;

use function array_merge;
use function count;

/**
 * Node Tree class for Laminas\Server reflection operations
 */
class Node
{
    /**
     * Node value
     *
     * @var mixed
     */
    protected $value;

    /**
     * Array of child nodes (if any)
     *
     * @var array
     */
    protected $children = [];

    /**
     * Parent node (if any)
     *
     * @var Node
     */
    protected $parent;

    /**
     * Constructor
     *
     * @param mixed $value
     * @param Node $parent Optional
     * @return Node
     */
    public function __construct($value, ?Node $parent = null)
    {
        $this->value = $value;
        if (null !== $parent) {
            $this->setParent($parent, true);
        }

        return $this;
    }

    /**
     * Set parent node
     *
     * //phpcs:disable SlevomatCodingStandard.Namespaces.ReferenceUsedNamesOnly.ReferenceViaFullyQualifiedName
     * //phpcs:disable SlevomatCodingStandard.TypeHints.ParameterTypeHint.UselessAnnotation
     * @param \Laminas\Server\Reflection\Node $node
     * //phpcs:enable SlevomatCodingStandard.TypeHints.ParameterTypeHint.UselessAnnotation
     * //phpcs:enable SlevomatCodingStandard.Namespaces.ReferenceUsedNamesOnly.ReferenceViaFullyQualifiedName
     * @param  bool $new Whether or not the child node is newly created
     * and should always be attached
     * @return void
     */
    public function setParent(Node $node, $new = false)
    {
        $this->parent = $node;

        if ($new) {
            $node->attachChild($this);
            return;
        }
    }

    /**
     * Create and attach a new child node
     *
     * @param mixed $value
     * @access public
     * @return Node New child node
     */
    public function createChild($value)
    {
        return new static($value, $this);
    }

    /**
     * Attach a child node
     *
     * @return void
     */
    public function attachChild(Node $node)
    {
        $this->children[] = $node;

        if ($node->getParent() !== $this) {
            $node->setParent($this);
        }
    }

    /**
     * Return an array of all child nodes
     *
     * @return array
     */
    public function getChildren()
    {
        return $this->children;
    }

    /**
     * Does this node have children?
     *
     * @return bool
     */
    public function hasChildren()
    {
        return count($this->children) > 0;
    }

    /**
     * Return the parent node
     *
     * @return null|Node
     */
    public function getParent()
    {
        return $this->parent;
    }

    /**
     * Return the node's current value
     *
     * @return mixed
     */
    public function getValue()
    {
        return $this->value;
    }

    /**
     * Set the node value
     *
     * @param mixed $value
     * @return void
     */
    public function setValue($value)
    {
        $this->value = $value;
    }

    /**
     * Retrieve the bottommost nodes of this node's tree
     *
     * Retrieves the bottommost nodes of the tree by recursively calling
     * getEndPoints() on all children. If a child is null, it returns the parent
     * as an end point.
     *
     * @return array
     */
    public function getEndPoints()
    {
        $endPoints = [];
        if (! $this->hasChildren()) {
            return $endPoints;
        }

        foreach ($this->children as $child) {
            $value = $child->getValue();

            if (null === $value) {
                $endPoints[] = $this;
            } elseif ($child->hasChildren()) {
                $childEndPoints = $child->getEndPoints();
                if (! empty($childEndPoints)) {
                    $endPoints = array_merge($endPoints, $childEndPoints);
                }
            } elseif (! $child->hasChildren()) {
                $endPoints[] = $child;
            }
        }

        return $endPoints;
    }
}
