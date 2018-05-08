<?php
declare(strict_types=1);
namespace wcf\system\form\builder;
use wcf\system\form\builder\field\dependency\IFormFieldDependency;

/**
 * Represents a general form node providing common methods of all nodes.
 * 
 * @author	Matthias Schmidt
 * @copyright	2001-2018 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	WoltLabSuite\Core\System\Form\Builder
 * @since	3.2
 */
interface IFormNode {
	/**
	 * Adds the given CSS class to this node and returns this node.
	 * 
	 * @param	string		$class		added CSS class name
	 * @return	static				this node
	 * 
	 * @throws	\InvalidArgumentException	if the given class is invalid
	 */
	public function addClass(string $class): IFormNode;
	
	/**
	 * Adds a dependency on the value of a `IFormField` so that this node is
	 * only available if the field satisfies the given dependency and returns
	 * this element.
	 * 
	 * This method is expected to set the dependent node of the given dependency
	 * to this element.
	 * 
	 * @param	IFormFieldDependency	$dependency	added node dependency
	 * @return	static					this node
	 */
	public function addDependency(IFormFieldDependency $dependency): IFormNode;
	
	/**
	 * Adds an additional attribute to this node and returns this node.
	 * 
	 * The value of an existing attribute is overwritten by the new value.
	 * 
	 * @param	string		$name		attribute name
	 * @param	null|string	$value		attribute value
	 * @return	static				this node
	 * 
	 * @throws	\InvalidArgumentException	if an invalid name or value is given (some attribute names are invalid as there are specific methods for setting that attribute)
	 */
	public function attribute(string $name, string $value = null): IFormNode;
	
	/**
	 * Sets if this node is available and returns this node.
	 * 
	 * By default, every node is available. This methods makes it easier to create forms
	 * that contains node that are only avaiable if certain options have specific values
	 * or the active user has specific permissions, for example. Furthermore, fields
	 * themselves are also able to mark themselves as unavailable, for example, a selection
	 * field without any options. A `IFormContainer` is automatically unavailable if it
	 * contains no available children.
	 * 
	 * Unavailable fields produce no output, their value is not read, they are not validated
	 * and they are not checked for save values.
	 * 
	 * Note: Form field dependencies manage dynamic availability of form nodes based on
	 * form field values while this method manages static availability that is independent
	 * of form field values and only depends on external factors.
	 * 
	 * @param	bool		$available	determines if node is available
	 * @return	static				this node
	 */
	public function available(bool $available = true): IFormNode;
	
	/**
	 * Returns `true` if the node's dependencies are met and returns `false` otherwise.
	 *
	 * @return	bool
	 */
	public function checkDependencies(): bool;
	
	/**
	 * Returns the value of the additional attribute of this node with the given name.
	 * 
	 * @param	string		$name		attribute name
	 * @return	mixed				attribute value
	 * 
	 * @throws	\InvalidArgumentException	if the given name is invalid or no such attribute exists
	 */
	public function getAttribute(string $name);
	
	/**
	 * Returns additional attributes of this node.
	 * 
	 * @return	array		additional node attributes
	 */
	public function getAttributes(): array;
	
	/**
	 * Returns all CSS classes of this node.
	 * 
	 * @return	string[]	CSS classes of node
	 */
	public function getClasses(): array;
	
	/**
	 * Returns all of the node's dependencies.
	 * 
	 * @return	IFormFieldDependency[]		node's dependencies
	 */
	public function getDependencies(): array;
	
	/**
	 * Returns the form document this node belongs to.
	 * 
	 * @return	IFormDocument			form document node belongs to
	 * 
	 * @throws	\BadMethodCallException		if form document is inaccessible for this node
	 */
	public function getDocument(): IFormDocument;
	
	/**
	 * Returns the html representation of this node.
	 *
	 * @return	string		html representation of node
	 */
	public function getHtml(): string;
	
	/**
	 * Returns additional template variables used to generate the html representation
	 * of this node.
	 * 
	 * @return	array		additional template variables
	 */
	public function getHtmlVariables(): array;
	
	/**
	 * Returns the id of the form node.
	 * 
	 * @return	string		node id
	 * 
	 * @throws	\BadMethodCallException		if no id has been set
	 */
	public function getId(): string;
	
	/**
	 * Returns the prefixed id of this node that means a combination of the form
	 * documents global prefix and this nodes ids.
	 * 
	 * The prefixed id is primarily intended to be used when outputting the form's
	 * fields and reading their values.
	 * 
	 * @return	string				prefixed node id
	 * 
	 * @throws	\BadMethodCallException		if no id has been set or if form document is inaccessible for this node
	 */
	public function getPrefixedId(): string;
	
	/**
	 * Returns `true` if an additional attribute with the given name exists and returns
	 * `false` otherwise.
	 * 
	 * @param	string		$name		attribute name
	 * @return	bool
	 * 
	 * @throws	\InvalidArgumentException	if the given attribute name is invalid
	 */
	public function hasAttribute(string $name): bool;
	
	/**
	 * Returns `true` if a CSS class with the given name exists and returns `false` otherwise.
	 * 
	 * @param	string		$class		checked CSS class
	 * @return	bool
	 * 
	 * @throws	\InvalidArgumentException	if the given class is invalid
	 */
	public function hasClass(string $class): bool;
	
	/**
	 * Returns `true` if this node has a dependency with the given id and
	 * returns `false` otherwise.
	 * 
	 * @param	string		$dependencyId	id of the checked dependency
	 * @return	bool
	 * 
	 * @throws	\InvalidArgumentException	if the given id is invalid
	 */
	public function hasDependency(string $dependencyId): bool;
	
	/**
	 * Sets the id of the node.
	 * 
	 * @param	string		$id		new id of node
	 * @return	static				this node
	 * 
	 * @throws	\BadMethodCallException		if id has already been set
	 * @throws	\InvalidArgumentException	if the given id is invalid
	 */
	public function id(string $id): IFormNode;
	
	/**
	 * Returns `true` if this node is available and returns `false` otherwise.
	 * 
	 * If the node's availability has not been explicitly set, `true` is returned.
	 * 
	 * @return	bool
	 * 
	 * @see		IFormNode::available()
	 */
	public function isAvailable(): bool;
	
	/**
	 * Is called once after all nodes have been added to the document this node belongs to.
	 * 
	 * This method enables this node to perform actions that require the whole document having
	 * finished constructing itself and every parent-child relationship being established.
	 * 
	 * @return	static				this node
	 * 
	 * @throws	\BadMethodCallException		if this node has already been populated
	 */
	public function populate(): IFormNode;
	
	/**
	 * Removes the given CSS class and returns this node.
	 * 
	 * If this node does not have the given CSS class, this method silently
	 * ignores that fact.
	 * 
	 * @param	string		$class		removed CSS class
	 * @return	static				this node
	 * 
	 * @throws	\InvalidArgumentException	if the given class is invalid
	 */
	public function removeClass(string $class): IFormNode;
	
	/**
	 * Removes the dependency with the given id and returns this node.
	 * 
	 * @param	string		$dependencyId	id of the removed dependency
	 * @return	static				this node
	 * 
	 * @throws	\InvalidArgumentException	if the given id is invalid or no such dependency exists
	 */
	public function removeDependency(string $dependencyId): IFormNode;
	
	/**
	 * Validates the node.
	 * 
	 * Note: A `IFormParentNode` object may only return `true` if all of its child
	 * nodes are valid. A `IFormField` object is valid if its value is valid.
	 */
	public function validate();
	
	/**
	 * Creates a new element with the given id.
	 * 
	 * @param	string		$id	node id
	 * @return	static			this node
	 * 
	 * @throws	\InvalidArgumentException	if the given id is already used by another element or otherwise is invalid
	 */
	public static function create(string $id): IFormNode;
	
	/**
	 * Checks if the given attribute name class a string and a valid attribute name.
	 * 
	 * @param	string		$name		checked argument name
	 * 
	 * @throws	\InvalidArgumentException	if the given attribute name is invalid
	 */
	public static function validateAttribute(string $name);
	
	/**
	 * Checks if the given parameter class a string and a valid node class.
	 * 
	 * @param	string		$class		checked class
	 * 
	 * @throws	\InvalidArgumentException	if the given id is invalid
	 */
	public static function validateClass(string $class);
	
	/**
	 * Checks if the given parameter is a string and a valid node id.
	 * 
	 * @param	string		$id		checked id
	 * 
	 * @throws	\InvalidArgumentException	if the given id is invalid
	 */
	public static function validateId(string $id);
}