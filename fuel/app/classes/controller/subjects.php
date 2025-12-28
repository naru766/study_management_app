<?php

class Controller_Subjects extends Controller_Base
{
  public function action_index()
  {
    $sort = (string) Input::get('sort', 'added');
	
    $records = Model_StudyRecord::get_all_with_sort($this->user_id, $sort);

    $this->template->title = '全科目一覧';
    $this->template->content = View::forge('subjects/index', [
      'records' => $records,
      'sort'    => $sort,
    ]);
  }
}
