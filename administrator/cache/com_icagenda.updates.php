<?php
class LiveUpdateCom_icagendaCache {
	public $update = array("stuck" => "0", "lastcheck" => "1427559021", "updatedata" => "Tzo4OiJzdGRDbGFzcyI6ODp7czo5OiJzdXBwb3J0ZWQiO2I6MTtzOjU6InN0dWNrIjtiOjA7czo3OiJ2ZXJzaW9uIjtzOjU6IjMuNS4zIjtzOjQ6ImRhdGUiO3M6MTA6IjIwMTUtMDMtMjUiO3M6OToic3RhYmlsaXR5IjtzOjY6InN0YWJsZSI7czoxMToiZG93bmxvYWRVUkwiO3M6NjQ6Imh0dHA6Ly93d3cuam9vbWxpYy5jb20vZW4vaWNycy9pY2FnZW5kYS0zLTUtMy9pY2FnZW5kYV8zLTUtMy16aXAiO3M6NzoiaW5mb1VSTCI7czo0NToiaHR0cDovL3d3dy5qb29tbGljLmNvbS9lbi9pY3JzL2ljYWdlbmRhLTMtNS0zIjtzOjEyOiJyZWxlYXNlbm90ZXMiO3M6Mzc4NToiPGgyPjxzdHJvbmc+PHNwYW4+PHNwYW4gc3R5bGU9ImNvbG9yOiAjOTkzMzAwOyI+aUM8L3NwYW4+PHNwYW4gc3R5bGU9ImNvbG9yOiAjODA4MDgwOyI+YWdlbmRhPHNwYW4gc3R5bGU9ImNvbG9yOiAjNjY2NjY2OyI+4oSiPC9zcGFuPjwvc3Bhbj4gMy41LjM8c3BhbiBzdHlsZT0iZm9udC1zaXplOiAxMHB0OyI+PC9zcGFuPjwvc3Bhbj48c3BhbiBzdHlsZT0iZm9udC1zaXplOiAxMHB0OyI+PC9zcGFuPjxiciAvPjwvc3Ryb25nPjxzcGFuIHN0eWxlPSJmb250LXNpemU6IDhwdDsgY29sb3I6ICMzMzMzMzM7Ij4yMDE1LjAzLjI1PC9zcGFuPjwvaDI+PGhyIC8+PHA+PHNwYW4gbGFuZz0iZW4iPjxzcGFuIGxhbmc9ImVuIj48c3Ryb25nPlRoaXMgaXMgYSBtYWludGVuYW5jZSByZWxlYXNlLiBPbmUgZmVhdHVyZSB3YXMgYWRkZWQgdG8gcmVnaXN0cmF0aW9uIGZvcm06IENvbmZpcm0gRW1haWwgZmllbGQgKHRoaXMgb25lIGlzIGVuYWJsZWQgYnkgZGVmYXVsdCwgYnV0IHlvdSBjYW4gdHVybiBpdCBvZmYgaW4gZ2xvYmFsIG9wdGlvbnMgb2YgY29tcG9uZW50KTxiciAvPjwvc3Ryb25nPldlIHJlY29tbWVuZCBldmVyeSB1c2VyIHRvIHVwZGF0ZSwgdG8gdGFrZSBhZHZhbnRhZ2Ugb2YgYWxsIHRoZSBlbmhhbmNlbWVudCBkb25lIGluIDMuNS4zPGJyIC8+KHNlZSBSZWxlYXNlIE5vdGVzKS48YnIgLz48L3NwYW4+PC9zcGFuPjwvcD48cD48c3Ryb25nPjxzcGFuIGxhbmc9ImVuIj48c3BhbiBsYW5nPSJlbiI+V2UgYWR2aXNlIHRoYXQgeW91wqBhbHdheXMgcGVyZm9ybSBhIGJhY2t1cCBiZWZvcmUgbWFraW5nIGFueSBjaGFuZ2VzIHRvIHlvdXIgc2l0ZS48YnIgLz48ZW0+PHNwYW4gc3R5bGU9ImNvbG9yOiAjZmYwMDAwOyI+PHNwYW4gc3R5bGU9ImNvbG9yOiAjMDAwMDAwOyI+TWluaW11bSBwaHAgdmVyc2lvbiA1LjMuMTAgaXMgcmVjb21tZW5kZWQuPGJyIC8+PC9zcGFuPjwvc3Bhbj48L2VtPllvdSBjYW4gdXNlIHRoaXMgdmVyc2lvbiBvbiBqb29tbGEgMi41LCAzLjIsIDMuMyBhbmQgMy40LiBJdCBpcyBhIGNyb3NzLXBsYXRmb3JtIHZlcnNpb24uPC9zcGFuPjwvc3Bhbj48YnIgLz48L3N0cm9uZz48c3Ryb25nPjxiciAvPjwvc3Ryb25nPjwvcD48cD48c3Ryb25nPjxzdHJvbmc+SU5GTzo8L3N0cm9uZz4gPC9zdHJvbmc+Sm9vbWxhISAyLjUgU3VwcG9ydCBJcyBFbmRpbmcgT24gRGVjZW1iZXIgMzEsIDIwMTQhIERvbid0IHdvcnJ5IGFib3V0IGlDYWdlbmRhLCB3ZSBhcmUga2VlcGluZyBzdXBwb3J0IGZvciAyLjUgaW4gZWFjaCBuZXcgcmVsZWFzZSBmb3IgYXQgbGVhc3QgNiBtb250aHMgdG8gbGV0IHlvdSB0aW1lIHRvIG1pZ3JhdGUgdG8gSm9vbWxhIDMgOiA8YSBocmVmPSJodHRwczovL2RvY3Muam9vbWxhLm9yZy9XaHlfTWlncmF0ZSIgdGFyZ2V0PSJfYmxhbmsiPmh0dHBzOi8vZG9jcy5qb29tbGEub3JnL1doeV9NaWdyYXRlPC9hPjwvcD48cD48c3Ryb25nPsKgPC9zdHJvbmc+PC9wPjxwPjxzdHJvbmc+PHN0cm9uZz48c3Ryb25nPjxzdHJvbmc+PHN0cm9uZz48c3Ryb25nPlJlbGVhc2Ugbm90ZXM8L3N0cm9uZz48L3N0cm9uZz48L3N0cm9uZz48L3N0cm9uZz48L3N0cm9uZz48L3N0cm9uZz48c3Ryb25nPjxzdHJvbmc+PHN0cm9uZz48c3Ryb25nPjxzdHJvbmc+PHN0cm9uZz48YnIgLz48L3N0cm9uZz48L3N0cm9uZz48L3N0cm9uZz48L3N0cm9uZz48L3N0cm9uZz48L3N0cm9uZz4rIEFkZGVkIDogQ29uZmlybSBFbWFpbCBmaWVsZCBpbiBmcm9udGVuZCBSZWdpc3RyYXRpb24gZm9ybSwgZm9yIG5vdCBsb2dnZWQtaW4gdXNlci48YnIgLz4rIEFkZGVkIDogQk9NIHV0Zi04IHRvIGNzdiBleHBvcnQgZmlsZSAoc3BlY2lhbCBjaGFyYWN0ZXJzKS48YnIgLz5+IENoYW5nZWQgOiBpbXByb3ZlbWVudCBvZiB0aGUgbW9kZWwgZm9yIGFkbWluIGV2ZW50IGVkaXRpb24uPGJyIC8+fiBDaGFuZ2VkIDogcG9zdGFsIGNvZGUgaXMgbm93IGRpc3BsYXllZCAoaWYgYXZhaWxhYmxlKSBpbiBmcm9udGVuZCBhZGRyZXNzIGZpZWxkLjxiciAvPiMgW01FRElVTV0gRml4ZWQgOiBzYXZpbmcgb2YgY3VzdG9tIGZpZWxkcyBhbmQgZmVhdHVyZXMgd2hlbiBuZXcgZXZlbnQgKHdpdGggbm90IHlldCBhbiBJRCkgd2FzIGJyb2tlbiAobm8gZGF0YSBzYXZlZCkuPGJyIC8+IyBbVEhFTUUgUEFDS1NdW0xPV10gRml4ZWQgOiBkaXNwbGF5IG9mIGVtcHR5IHBhcnRpY2lwYW50cyBsaXN0IHdoZW4gcmVnaXN0cmF0aW9uIG5vdCBlbmFibGVkLjxiciAvPiMgW0xPV10gRml4ZWQgOiBkaXNwbGF5IG9mIGluZm9ybWF0aW9uIGRldGFpbHMgaW4gZXZlbnQgZGV0YWlscyB2aWV3LCB3YXMgbm90IGFsd2F5cyBkaXNwbGF5ZWQgZGVwZW5kaW5nIG9mIG9wdGlvbnMgYW5kIGRhdGEgZmlsbGVkLjxiciAvPiMgW0xPV10gRml4ZWQgOiByZWdpc3RlciBidXR0b24gd2hlbiBvbmx5IGEgc2luZ2xlIGRhdGUsIGFuZCB0aGUgbGlzdCBkaXNwbGF5IHR5cGUgaXMgbm90IHNldCB0byBkaXNwbGF5IGFsbCBkYXRlcy48YnIgLz4jIFtMT1ddIEZpeGVkIDogYWRkZWQgYmFjayB0aGUgYWxlcnQgbWVzc2FnZSBvbiBKb29tbGEgMi41IGFib3V0IHRoZSBpbXBvc3NpYmlsaXR5IG9mIHRyYXNoaW5nIGZyb250ZW5kIHN1Ym1pdHRlZCBldmVudHMgaWYgbm90IGVkaXRlZCAodGhlIGlzc3VlIHdpdGggdHJhc2ggYW5kIGVtcHR5IGFzc2V0X2lkIGlzIGZpeGVkIGluIGxhdGVzdCB2ZXJzaW9uIG9mIEpvb21sYSAzKS48L3A+PGRpdj7CoDwvZGl2PjxkaXY+PHN0cm9uZz5DaGFuZ2VkIGZpbGVzIGluIDMuNS4zPGJyIC8+PC9zdHJvbmc+fiBhZG1pbi9jb25maWcueG1sPGJyIC8+fiBhZG1pbi9tb2RlbHMvZXZlbnQucGhwPGJyIC8+fiBhZG1pbi9tb2RlbHMvZmllbGRzL21vZGFsL2ljdGV4dF9wbGFjZWhvbGRlci5waHA8YnIgLz5+IGFkbWluL21vZGVscy9mb3Jtcy9ldmVudC54bWw8YnIgLz5+IGFkbWluL21vZGVscy9yZWdpc3RyYXRpb25zLnBocDxiciAvPn4gYWRtaW4vdGFibGVzL2V2ZW50LnBocDxiciAvPn4gYWRtaW4vdXRpbGl0aWVzL2V2ZW50cy9kYXRhLnBocDxiciAvPn4gYWRtaW4vdmlld3MvZXZlbnRzL3RtcGwvZGVmYXVsdC5waHA8YnIgLz5+IGFkbWluL3ZpZXdzL3JlZ2lzdHJhdGlvbnMvdmlldy5yYXcucGhwPGJyIC8+fiBbTU9EVUxFXSBtb2R1bGVzL21vZF9pY2NhbGVuZGFyL2hlbHBlci5waHA8YnIgLz5+IHNpdGUvYWRkL2VsZW1lbnRzL2ljc2V0dmFyLnBocDxiciAvPn4gc2l0ZS9oZWxwZXJzL2ljaGVscGVyLnBocDxiciAvPn4gc2l0ZS9oZWxwZXJzL2ljbW9kZWwucGhwPGJyIC8+fiBzaXRlL21vZGVscy9saXN0LnBocDxiciAvPn4gW1RIRU1FIFBBQ0tTXSBzaXRlL3RoZW1lcy9wYWNrcy9kZWZhdWx0L2RlZmF1bHRfZXZlbnQucGhwPGJyIC8+fiBbVEhFTUUgUEFDS1NdIHNpdGUvdGhlbWVzL3BhY2tzL2ljX3JvdW5kZWQvaWNfcm91bmRlZF9ldmVudC5waHA8YnIgLz5+IHNpdGUvdmlld3MvbGlzdC90bXBsL2V2ZW50LnBocDxiciAvPn4gc2l0ZS92aWV3cy9saXN0L3RtcGwvcmVnaXN0cmF0aW9uLnBocDxiciAvPn4gc2l0ZS92aWV3cy9saXN0L3ZpZXcuaHRtbC5waHA8L2Rpdj48cD7CoDwvcD48aHIgLz48cD48c3BhbiBzdHlsZT0iY29sb3I6ICM4MDgwODA7Ij48ZW0+PHNwYW4gc3R5bGU9ImZvbnQtc2l6ZTogOHB0OyI+SWYgeW91IGVuY291bnRlciBhIGJ1ZywgdGhhbmtzIHRvIHJlcG9ydCBpdCBvbiB0aGUgSm9vbWxpQyBmb3J1bSwgc28gdGhhdCBpIGNhbiBwcm92aWRlIGEgZml4IGFzIGZhc3QgYXMgcG9zc2libGUuPC9zcGFuPjwvZW0+PC9zcGFuPjwvcD48cD7CoDwvcD4iO30=");
}
?>