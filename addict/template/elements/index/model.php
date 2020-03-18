<?
class Model_Pages_Index extends Model
{
	public function get_data()
	{	
		GLOBAL $TMPL;
		return Array("assets"=>$TMPL->link()."assets");
	}
}
?>