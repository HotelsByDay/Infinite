<?php

/**
 * Controller pro testovaci ucely
 * - nic na nem neni zavysle a je mozne ho odstranit
 */
class Controller_Test extends Controller_Layout
{
    
    public function action_index()
    {
        
        $data = ORM::factory('advert')->find_all();
        
        $metadata = Array(
            Array(
                'attr' => 'code',
                'label' => 'Kod nabidky',
            ),
            Array(
                'attr' => function($orm) {return $orm->seller->_name;},
                'label' => 'Makléř',
            ),
            
        );
        
        $exp = Exporter::factory('csv');
        
        $file = $exp->generate('test.test.csv', $metadata, $data);
        
        $this->request->redirect($file);
                
        $this->template->content = 'aaa';
        
    }
    
}
?>
