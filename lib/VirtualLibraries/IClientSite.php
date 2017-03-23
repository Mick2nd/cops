<?php
namespace VirtualLibraries;

/// <summary>
/// Use this iface to query an outside db about book
/// </summary>
interface IClientSite
{
	public function create($parseString);
	public function isSelected($id);
	public function test($sql);
	public function getIds($sql);
}
