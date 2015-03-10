<?php
namespace Core;

class BugRelationshipData {
	/**
	 * Relationship id
	 */
	public $id;

	/**
	 * Source Bug id
	 */
	public $src_bug_id;

	/**
	 * Source project id
	 */
	public $src_project_id;

	/**
	 * Destination Bug id
	 */
	public $dest_bug_id;

	/**
	 * Destination project id
	 */
	public $dest_project_id;

	/**
	 * Type
	 */
	public $type;
}