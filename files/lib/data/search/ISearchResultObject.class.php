<?php
namespace wcf\data\search;

interface ISearchResultObject {
	public function getUserProfile();
	
	public function getSubject();
	
	public function getTime();
	
	public function getLink($query = '');
	
	public function getObjectTypeName();
	
	public function getFormattedMessage();
	
	public function getContainerTitle();
	
	public function getContainerLink();
}