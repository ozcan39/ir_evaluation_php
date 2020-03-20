<?php
namespace ir_evaluation;

class effectiveness{
    public function ap_at_n($interactions,$boundaries=array("all"))
    {
        $ap=array();
        $m_ap=array();//mean ap@n

        foreach ($interactions as $query)
        {
            $total_result=$query['total_result'];//list: total count of results
            $related_documents=array_values(array_unique($query['related_documents']));//the list of documents which is known that related to the query

            //visited_documents: id of each visited page on the result list
            //the reason of using array_unique is to ignore the same page visits (happened repeatedly) in the same query
            $visited_documents=array_values(array_unique($query['visited_documents']));

            //visited_documents_orders: the order of each visited page, which it's id is known, on the result list
            $visited_documents_orders=$query['visited_documents_orders'];

            foreach ($boundaries as $cutoff)
            {
                if(is_numeric($cutoff))
                {
                    if($total_result>=$cutoff)
                    {
                        $related=0;

                        foreach ($visited_documents as $document_id)
                        {
                            if(in_array($document_id,$related_documents) and $visited_documents_orders[$document_id]<=$cutoff)
                            {
                                $related++;
                            }
                        }

                        $ap[$cutoff][]=$related/$cutoff;
                    }
                }
                else
                {
                    $related=0;

                    foreach ($visited_documents as $document_id)
                    {
                        if(in_array($document_id,$related_documents)) { $related++; }
                    }

                    $ap[$cutoff][]=$related/$total_result;
                }
            }
        }

        //m_ap calculation over all organised ap values
        foreach ($boundaries as $cutoff)
        {
            $m_ap[$cutoff]=array('count'=>0,'value'=>0);

            //is there any value for the related cutoff
            if(count($ap[$cutoff])>0)
            {
                $m_ap[$cutoff]['count']=count($ap[$cutoff]);//how many ap was used
                $m_ap[$cutoff]['value']=array_sum($ap[$cutoff])/count($ap[$cutoff]);
            }
        }

        return $m_ap;
    }

    public function mean_ap($interactions,$boundaries=array("all"))
    {
        $ap=array();
        $map=array();

        foreach ($interactions as $query)
        {
            $total_result=$query['total_result'];//list: total count of results
            $related_documents=array_values(array_unique($query['related_documents']));//the list of documents which is known that related to the query

            //visited_documents: id of each visited page on the result list
            //the reason of using array_unique is to ignore the same page visits (happened repeatedly) in the same query
            $visited_documents=array_values(array_unique($query['visited_documents']));

            //visited_documents_orders: the order of each visited page, which it's id is known, on the result list
            $visited_documents_orders=$query['visited_documents_orders'];
            asort($visited_documents_orders);

            foreach ($boundaries as $cutoff)
            {
                $pass=$visited_documents;

                if(is_numeric($cutoff))
                {
                    if($total_result>=$cutoff)
                    {
                        $ap[$cutoff][]=array('value'=>0,'count'=>0);
                        $index=count($ap[$cutoff])-1;

                        foreach ($visited_documents_orders as $document_id => $order)
                        {
                            if(in_array($document_id,$pass) and in_array($document_id,$related_documents) and $order<=$cutoff)
                            {
                                $ap[$cutoff][$index]['count']++;
                                $ap[$cutoff][$index]['value']+=$ap[$cutoff][$index]['count']/$order;

                                $pass=array_diff($pass,array($document_id));
                            }
                        }

                        $ap[$cutoff][$index]['value']/=count($related_documents);
                    }
                }
                else
                {
                    $ap[$cutoff][]=array('value'=>0,'count'=>0);
                    $index=count($ap[$cutoff])-1;

                    foreach ($visited_documents_orders as $document_id => $order)
                    {
                        if(in_array($document_id,$pass) and in_array($document_id,$related_documents))
                        {
                            $ap[$cutoff][$index]['count']++;
                            $ap[$cutoff][$index]['value']+=$ap[$cutoff][$index]['count']/$order;

                            $pass=array_diff($pass,array($document_id));
                        }
                    }

                    $ap[$cutoff][$index]['value']/=count($related_documents);
                }
            }
        }

        //map calculation over all organised ap values
        foreach ($boundaries as $cutoff)
        {
            $map[$cutoff]=array('count'=>0,'value'=>0);
            $total[$cutoff]=0;

            //is there any value for the related cutoff
            if(count($ap[$cutoff])>0)
            {
                for($i=0;$i<count($ap[$cutoff]);$i++) { $total[$cutoff]+=$ap[$cutoff][$i]['value']; }

                $map[$cutoff]['count']=count($ap[$cutoff]);//how many ap was used
                $map[$cutoff]['value']=$total[$cutoff]/count($ap[$cutoff]);
            }
        }

        return $map;
    }

    public function gmap($interactions,$constant=0.01,$boundaries=array("all"))
    {
        $ap=array();

        foreach ($interactions as $query)
        {
            $total_result=$query['total_result'];//list: total count of results
            $related_documents=array_values(array_unique($query['related_documents']));//the list of documents which is known that related to the query

            //visited_documents: id of each visited page on the result list
            //the reason of using array_unique is to ignore the same page visits (happened repeatedly) in the same query
            $visited_documents=array_values(array_unique($query['visited_documents']));

            //visited_documents_orders: the order of each visited page, which it's id is known, on the result list
            $visited_documents_orders=$query['visited_documents_orders'];
            asort($visited_documents_orders);

            foreach ($boundaries as $cutoff)
            {
                $pass=$visited_documents;

                if(is_numeric($cutoff))
                {
                    if($total_result>=$cutoff)
                    {
                        $ap[$cutoff][]=array('value'=>0,'count'=>0);
                        $index=count($ap[$cutoff])-1;

                        foreach ($visited_documents_orders as $document_id => $order)
                        {
                            if(in_array($document_id,$pass) and in_array($document_id,$related_documents) and $order<=$cutoff)
                            {
                                $ap[$cutoff][$index]['count']++;
                                $ap[$cutoff][$index]['value']+=$ap[$cutoff][$index]['count']/$order;

                                $pass=array_diff($pass,array($document_id));
                            }
                        }

                        $ap[$cutoff][$index]['value']/=count($related_documents);
                    }
                }
                else
                {
                    $ap[$cutoff][]=array('value'=>0,'count'=>0);
                    $index=count($ap[$cutoff])-1;

                    foreach ($visited_documents_orders as $document_id => $order)
                    {
                        if(in_array($document_id,$pass) and in_array($document_id,$related_documents))
                        {
                            $ap[$cutoff][$index]['count']++;
                            $ap[$cutoff][$index]['value']+=$ap[$cutoff][$index]['count']/$order;

                            $pass=array_diff($pass,array($document_id));
                        }
                    }

                    $ap[$cutoff][$index]['value']/=count($related_documents);
                }
            }
        }

        //gmap calculation over all organised ap values
        foreach ($boundaries as $cutoff)
        {
            $gmap[$cutoff]=array('count'=>0,'value'=>0);
            $total[$cutoff]=1;

            //is there any value for the related cutoff
            if(count($ap[$cutoff])>0)
            {
                for($i=0;$i<count($ap[$cutoff]);$i++)
                {
                    if($ap[$cutoff][$i]['value']!=0) { $total[$cutoff]*=$ap[$cutoff][$i]['value']+$constant; }
                    else { $total[$cutoff]*=$constant; }
                }

                $gmap[$cutoff]['count']=count($ap[$cutoff]);//how many ap was used
                $gmap[$cutoff]['value']=pow($total[$cutoff],1/count($ap[$cutoff]));
            }
        }

        return $gmap;
    }

    public function iap($interactions)
    {
        $iap=array();
        $ep=array('0.0'=>0,'0.1'=>0,'0.2'=>0,'0.3'=>0,'0.4'=>0,'0.5'=>0,'0.6'=>0,'0.7'=>0,'0.8'=>0,'0.9'=>0,'1.0'=>0);
        $p_iap=array();

        foreach ($interactions as $query)
        {
            $related_documents=array_values(array_unique($query['related_documents']));//the list of documents which is known that related to the query

            //visited_documents: id of each visited page on the result list
            //the reason of using array_unique is to ignore the same page visits (happened repeatedly) in the same query
            $visited_documents=array_values(array_unique($query['visited_documents']));

            //visited_documents_orders: the order of each visited page, which it's id is known, on the result list
            $visited_documents_orders=$query['visited_documents_orders'];

            $p_iap[]=array('count'=>0, 'rp'=>array(),'ep'=>$ep);
            $index=count($p_iap)-1;

            foreach ($visited_documents as $document_id)
            {
                if(in_array($document_id,$related_documents))
                {
                    $p_iap[$index]['count']++;

                    //calculation is stored as recall=>precision
                    $recall=$p_iap[$index]['count']/count($related_documents);
                    $p_iap[$index]['rp'][(string)$recall]=$p_iap[$index]['count']/$visited_documents_orders[$document_id];
                }
            }

            krsort($p_iap[$index]['rp']);

            $bprec=0;//the biggest precision is going to be hold
            foreach ($p_iap[$index]['rp'] as $recall=>$precision)
            {
                if($bprec==0) { $bprec=$precision; }

                if($bprec<$precision)
                {
                    $bprec=$precision;
                    $p_iap[$index]['rp'][$recall]=$bprec;
                }
                else { $p_iap[$index]['rp'][$recall]=$bprec; }
            }

            ksort($p_iap[$index]['rp']);

            $pass=array();
            foreach ($p_iap[$index]['rp'] as $trecall=>$tprecision)
            {
                foreach ($p_iap[$index]['ep'] as $recall=>$precision)
                {
                    if(!in_array($recall,$pass))
                    {
                        if($recall<=(float)$trecall)
                        {
                            $p_iap[$index]['ep'][$recall]=$tprecision;
                            $pass[]=$recall;
                        }
                        else { break; }
                    }
                }
            }
        }


        $total=$ep;
        if(count($p_iap)>0)
        {
            for($i=0;$i<count($p_iap);$i++)
            {
                foreach ($p_iap[$i]['ep'] as $recall=>$precision) { $total[$recall]+=$precision; }
            }

            foreach ($total as $recall=>$precision)
            {
                $iap[$recall]=$precision/count($p_iap);
            }
        }
        else
        {
            foreach ($ep as $recall=>$precision)
            {
                $iap[$recall]=0;
            }
        }

        return $iap;
    }

    public function rprecision($interactions,$boundaries=array("all"))
    {
        $rprecision=array();
        $m_rprecision=array();//mean rprecision

        foreach ($interactions as $query)
        {
            $total_result=$query['total_result'];//list: total count of results
            $related_documents=array_values(array_unique($query['related_documents']));//the list of documents which is known that related to the query

            //visited_documents: id of each visited page on the result list
            //the reason of using array_unique is to ignore the same page visits (happened repeatedly) in the same query
            $visited_documents=array_values(array_unique($query['visited_documents']));

            //visited_documents_orders: the order of each visited page, which it's id is known, on the result list
            $visited_documents_orders=$query['visited_documents_orders'];

            foreach ($boundaries as $cutoff)
            {
                if(is_numeric($cutoff))
                {
                    if($total_result>=$cutoff)
                    {
                        $related=0;

                        foreach ($visited_documents as $document_id)
                        {
                            if(in_array($document_id,$related_documents) and $visited_documents_orders[$document_id]<=$cutoff)
                            {
                                $related++;
                            }
                        }

                        $rprecision[$cutoff][]=$related/count($related_documents);
                    }
                }
                else
                {
                    $related=0;

                    foreach ($visited_documents as $document_id)
                    {
                        if(in_array($document_id,$related_documents)) { $related++; }
                    }

                    $rprecision[$cutoff][]=$related/count($related_documents);
                }
            }
        }

        //mean rprecision calculation over all organised rprecision values
        foreach ($boundaries as $cutoff)
        {
            $m_rprecision[$cutoff]=array('count'=>0,'value'=>0);

            //is there any value for the related cutoff
            if(count($rprecision[$cutoff])>0)
            {
                $m_rprecision[$cutoff]['count']=count($rprecision[$cutoff]);//how many rprecision was used
                $m_rprecision[$cutoff]['value']=array_sum($rprecision[$cutoff])/count($rprecision[$cutoff]);
            }
        }

        return $m_rprecision;
    }

    public function fmeasure($interactions,$boundaries=array("all"))
    {
        $fs=array();
        $fmeasure=array();

        foreach ($interactions as $query)
        {
            $total_result=$query['total_result'];//list: total count of results
            $related_documents=array_values(array_unique($query['related_documents']));//the list of documents which is known that related to the query

            //visited_documents: id of each visited page on the result list
            //the reason of using array_unique is to ignore the same page visits (happened repeatedly) in the same query
            $visited_documents=array_values(array_unique($query['visited_documents']));

            //visited_documents_orders: the order of each visited page, which it's id is known, on the result list
            $visited_documents_orders=$query['visited_documents_orders'];

            foreach ($boundaries as $cutoff)
            {
                if(is_numeric($cutoff))
                {
                    if($total_result>=$cutoff)
                    {
                        $related=0;

                        foreach ($visited_documents as $document_id)
                        {
                            if(in_array($document_id,$related_documents) and $visited_documents_orders[$document_id]<=$cutoff)
                            {
                                $related++;
                            }
                        }

                        $precision=$related/$cutoff;
                        $recall=$related/count($related_documents);
                        $p_plus_r=$precision+$recall;

                        //2*precision*recall/precision+recall
                        if($p_plus_r>0) { $fs[$cutoff][]=(2*$precision*$recall)/$p_plus_r; }
                        else { $fs[$cutoff][]=0; }
                    }
                }
                else
                {
                    $related=0;

                    foreach ($visited_documents as $document_id)
                    {
                        if(in_array($document_id,$related_documents)) { $related++; }
                    }

                    $precision=$related/$total_result;
                    $recall=$related/count($related_documents);
                    $p_plus_r=$precision+$recall;

                    //2*precision*recall/precision+recall
                    if($p_plus_r>0) { $fs[$cutoff][]=(2*$precision*$recall)/$p_plus_r; }
                    else { $fs[$cutoff][]=0; }
                }
            }
        }

        //fmeasure calculation over all organised fs values
        foreach ($boundaries as $cutoff)
        {
            $fmeasure[$cutoff]=array('count'=>0,'value'=>0);

            //is there any value for the related cutoff
            if(count($fs[$cutoff])>0)
            {
                $fmeasure[$cutoff]['count']=count($fs[$cutoff]);//how many fs was used
                $fmeasure[$cutoff]['value']=array_sum($fs[$cutoff])/count($fs[$cutoff]);
            }
        }

        return $fmeasure;
    }

    public function cgain($interactions,$boundaries=array("all"))
    {
        $cg=array();
        $cgain=array();

        foreach ($interactions as $query)
        {
            $total_result=$query['total_result'];//list: total count of results
            $assessed_documents=$query['assessed_documents'];//the list of documents ,which is assesed by a specialist or a user, related to the query

            $orders=array(); $assessments=array();
            foreach ($assessed_documents as $document=>$details)
            {
                $orders[]=$details[0];
                $assessments[]=$details[1];
            }

            foreach ($boundaries as $cutoff)
            {
                if(is_numeric($cutoff))
                {
                    if($total_result>=$cutoff)
                    {
                        $cg[$cutoff][]=0;
                        $index=count($cg[$cutoff])-1;

                        for($i=1;$i<=$cutoff;$i++)
                        {
                            if(in_array($i,$orders))
                            {
                                $cg[$cutoff][$index]+=$assessments[array_search($i, $orders)];
                            }
                        }
                    }
                }
                else
                {
                    $cg[$cutoff][]=0;
                    $index=count($cg[$cutoff])-1;

                    for($i=1;$i<=$total_result;$i++)
                    {
                        if(in_array($i,$orders))
                        {
                            $cg[$cutoff][$index]+=$assessments[array_search($i, $orders)];
                        }
                    }
                }
            }
        }

        //cgain calculation over all organised cg values
        foreach ($boundaries as $cutoff)
        {
            $cgain[$cutoff]=array('count'=>0,'value'=>0);

            //is there any value for the related cutoff
            if(count($cg[$cutoff])>0)
            {
                $cgain[$cutoff]['count']=count($cg[$cutoff]);//how many cg was used
                $cgain[$cutoff]['value']=array_sum($cg[$cutoff])/count($cg[$cutoff]);
            }
        }

        return $cgain;
    }

    public function ncgain($interactions,$boundaries=array("all"))
    {
        $ncgain=array();
        $ncg=array();
        $encg=array();

        foreach ($interactions as $query)
        {
            $total_result=$query['total_result'];//list: total count of results
            $assessed_documents=$query['assessed_documents'];//the list of documents ,which is assesed by a specialist or a user, related to the query

            $orders=array(); $assessments=array(); $expected_assessments=array();
            foreach ($assessed_documents as $document=>$details)
            {
                $orders[]=$details[0];
                $assessments[]=$details[1];
                $expected_assessments[]=$details[1];
            }
            arsort($expected_assessments);

            foreach ($boundaries as $cutoff)
            {
                if(is_numeric($cutoff))
                {
                    if($total_result>=$cutoff)
                    {
                        $ncg[$cutoff][]=0;
                        $encg[$cutoff][]=0;
                        $index=count($ncg[$cutoff])-1;

                        for($i=1;$i<=$cutoff;$i++)
                        {
                            if(in_array($i,$orders))
                            {
                                $ncg[$cutoff][$index]+=$assessments[array_search($i, $orders)];
                            }

                            if(isset($expected_assessments[($i-1)])) { $encg[$cutoff][$index]+=$expected_assessments[($i-1)]; }
                        }
                    }
                }
                else
                {
                    $ncg[$cutoff][]=0;
                    $encg[$cutoff][]=0;
                    $index=count($ncg[$cutoff])-1;

                    for($i=1;$i<=$total_result;$i++)
                    {
                        if(in_array($i,$orders))
                        {
                            $ncg[$cutoff][$index]+=$assessments[array_search($i, $orders)];
                        }

                        if(isset($expected_assessments[($i-1)])) { $encg[$cutoff][$index]+=$expected_assessments[($i-1)]; }
                    }
                }
            }
        }

        //ncgain calculation over all organised ncg values
        foreach ($boundaries as $cutoff)
        {
            $ncgain[$cutoff]=array('count'=>0,'value'=>0);
            $total=0;

            //is there any value for the related cutoff
            if(count($ncg[$cutoff])>0)
            {
                for($i=0;$i<count($ncg[$cutoff]);$i++)
                {
                    $total+=$ncg[$cutoff][$i]/$encg[$cutoff][$i];
                }

                $ncgain[$cutoff]['count']=count($ncg[$cutoff]);//how many ncg was used
                $ncgain[$cutoff]['value']=$total/count($ncg[$cutoff]);
            }
        }

        return $ncgain;
    }

    public function dcgain($interactions,$boundaries=array("all"))
    {
        $dcgain=array();
        $dcg=array();

        foreach ($interactions as $query)
        {
            $total_result=$query['total_result'];//list: total count of results
            $assessed_documents=$query['assessed_documents'];//the list of documents ,which is assesed by a specialist or a user, related to the query

            $orders=array(); $assessments=array();
            foreach ($assessed_documents as $document=>$details)
            {
                $orders[]=$details[0];
                $assessments[]=$details[1];
            }

            foreach ($boundaries as $cutoff)
            {
                if(is_numeric($cutoff))
                {
                    if($total_result>=$cutoff)
                    {
                        $dcg[$cutoff][]=0;
                        $index=count($dcg[$cutoff])-1;

                        for($i=1;$i<=$cutoff;$i++)
                        {
                            if(in_array($i,$orders))
                            {
                                if($i==1) { $dcg[$cutoff][$index]+=$assessments[array_search($i, $orders)]; }
                                else { $dcg[$cutoff][$index]+=$assessments[array_search($i, $orders)]/log($i,2); }
                            }
                        }
                    }
                }
                else
                {
                    $dcg[$cutoff][]=0;
                    $index=count($dcg[$cutoff])-1;

                    for($i=1;$i<=$total_result;$i++)
                    {
                        if(in_array($i,$orders))
                        {
                            if($i==1) { $dcg[$cutoff][$index]+=$assessments[array_search($i, $orders)]; }
                            else { $dcg[$cutoff][$index]+=$assessments[array_search($i, $orders)]/log($i,2); }
                        }
                    }
                }
            }
        }

        //dcgain calculation over all organised dcg values
        foreach ($boundaries as $cutoff)
        {
            $dcgain[$cutoff]=array('count'=>0,'value'=>0);

            //is there any value for the related cutoff
            if(count($dcg[$cutoff])>0)
            {
                $dcgain[$cutoff]['count']=count($dcg[$cutoff]);//how many dcg was used
                $dcgain[$cutoff]['value']=array_sum($dcg[$cutoff])/count($dcg[$cutoff]);
            }
        }

        return $dcgain;
    }

    public function ndcgain($interactions,$boundaries=array("all"))
    {
        $ndcgain=array();
        $dcg=array();
        $edcg=array();

        foreach ($interactions as $query)
        {
            $total_result=$query['total_result'];//list: total count of results
            $assessed_documents=$query['assessed_documents'];//the list of documents ,which is assesed by a specialist or a user, related to the query

            $orders=array(); $assessments=array(); $expected_assessments=array();
            foreach ($assessed_documents as $document=>$details)
            {
                $orders[]=$details[0];
                $assessments[]=$details[1];
                $expected_assessments[]=$details[1];
            }
            arsort($expected_assessments);

            foreach ($boundaries as $cutoff)
            {
                if(is_numeric($cutoff))
                {
                    if($total_result>=$cutoff)
                    {
                        $dcg[$cutoff][]=0;
                        $edcg[$cutoff][]=0;
                        $index=count($dcg[$cutoff])-1;

                        for($i=1;$i<=$cutoff;$i++)
                        {
                            if(in_array($i,$orders))
                            {
                                if($i==1) { $dcg[$cutoff][$index]+=$assessments[array_search($i, $orders)]; }
                                else { $dcg[$cutoff][$index]+=$assessments[array_search($i, $orders)]/log($i,2); }
                            }

                            if($i==1) { if(isset($expected_assessments[($i-1)])) { $edcg[$cutoff][$index]+=$expected_assessments[($i-1)]; } }
                            else { if(isset($expected_assessments[($i-1)])) { $edcg[$cutoff][$index]+=$expected_assessments[($i-1)]/log($i,2); } }
                        }
                    }
                }
                else
                {
                    $dcg[$cutoff][]=0;
                    $edcg[$cutoff][]=0;
                    $index=count($dcg[$cutoff])-1;

                    for($i=1;$i<=$total_result;$i++)
                    {
                        if(in_array($i,$orders))
                        {
                            if($i==1) { $dcg[$cutoff][$index]+=$assessments[array_search($i, $orders)]; }
                            else { $dcg[$cutoff][$index]+=$assessments[array_search($i, $orders)]/log($i,2); }
                        }

                        if($i==1) { if(isset($expected_assessments[($i-1)])) { $edcg[$cutoff][$index]+=$expected_assessments[($i-1)]; } }
                        else { if(isset($expected_assessments[($i-1)])) { $edcg[$cutoff][$index]+=$expected_assessments[($i-1)]/log($i,2); } }
                    }
                }
            }
        }

        //ndcgain calculation over all organised dcg values
        foreach ($boundaries as $cutoff)
        {
            $ndcgain[$cutoff]=array('count'=>0,'value'=>0);
            $total=0;

            //is there any value for the related cutoff
            if(count($dcg[$cutoff])>0)
            {
                for($i=0;$i<count($dcg[$cutoff]);$i++)
                {
                    $total+=$dcg[$cutoff][$i]/$edcg[$cutoff][$i];
                }

                $ndcgain[$cutoff]['count']=count($dcg[$cutoff]);//how many dcg was used
                $ndcgain[$cutoff]['value']=$total/count($dcg[$cutoff]);
            }
        }

        return $ndcgain;
    }

    public function mrr($interactions)
    {
        $total_inteaction=0;
        $rr=0;

        foreach ($interactions as $query)
        {
            //first_visit: the order number of first visited page on the result list
            $first_visit=$query['visited_documents_orders'][0];

            $rr+=1/$first_visit;
            $total_inteaction++;
        }

        //mrr calculation over rr value
        $mrr=$rr/$total_inteaction;

        return $mrr;
    }

    public function rbprecision($interactions,$p=array(0.5,0.8,0.95),$boundaries=array("all"))
    {
        $rbprecision=array();
        $m_rbprecision=array();//mean rbprecision

        foreach ($interactions as $query)
        {
            $total_result=$query['total_result'];//list: total count of results

            //visited_documents: id of each visited page on the result list
            //the reason of using array_unique is to ignore the same page visits (happened repeatedly) in the same query
            $visited_documents=array_values(array_unique($query['visited_documents']));

            //visited_documents_orders: the order of each visited page, which it's id is known, on the result list
            $visited_documents_orders=$query['visited_documents_orders'];
            asort($visited_documents_orders);

            foreach ($boundaries as $cutoff)
            {
                if(is_numeric($cutoff))
                {
                    if($total_result>=$cutoff)
                    {
                        foreach ($p as $values)
                        {
                            $pass=$visited_documents;

                            $rbprecision[$cutoff][(string)$values][]=0;
                            $index=count($rbprecision[$cutoff][(string)$values])-1;

                            foreach ($visited_documents_orders as $document_id => $order)
                            {
                                if(in_array($document_id,$pass) and $order<=$cutoff)
                                {
                                    if($order==1) { $rbprecision[$cutoff][(string)$values][$index]+=1; }
                                    else { $rbprecision[$cutoff][(string)$values][$index]+=pow($values,($order-1)); }

                                    $pass=array_diff($pass,array($document_id));
                                }
                            }

                            $rbprecision[$cutoff][(string)$values][$index]*=(1-$values);
                        }
                    }
                }
                else
                {
                    foreach ($p as $values)
                    {
                        $pass=$visited_documents;

                        $rbprecision[$cutoff][(string)$values][]=0;
                        $index=count($rbprecision[$cutoff][(string)$values])-1;

                        foreach ($visited_documents_orders as $document_id => $order)
                        {
                            if(in_array($document_id,$pass))
                            {
                                if($order==1) { $rbprecision[$cutoff][(string)$values][$index]+=1; }
                                else { $rbprecision[$cutoff][(string)$values][$index]+=pow($values,($order-1)); }

                                $pass=array_diff($pass,array($document_id));
                            }
                        }

                        $rbprecision[$cutoff][(string)$values][$index]*=(1-$values);
                    }
                }
            }
        }

        //mean rbprecision calculation over all organised rbprecision values
        foreach ($boundaries as $cutoff)
        {
            foreach ($p as $values)
            {
                $m_rbprecision[$cutoff][(string)$values]=array('count'=>0,'value'=>0);

                //is there any value for the related cutoff
                if(count($rbprecision[$cutoff][(string)$values])>0)
                {
                    $m_rbprecision[$cutoff][(string)$values]['count']=count($rbprecision[$cutoff][(string)$values]);//how many rbprecision was used
                    $m_rbprecision[$cutoff][(string)$values]['value']=array_sum($rbprecision[$cutoff][(string)$values])/count($rbprecision[$cutoff][(string)$values]);
                }
            }
        }

        return $m_rbprecision;
    }

    public function err($interactions,$max_grade=5,$boundaries=array("all"))
    {
        $err=array();
        $m_err=array(); // mean err

        foreach ($interactions as $query)
        {
            $total_result=$query['total_result'];//list: total count of results
            $assessed_documents=$query['assessed_documents'];//the list of documents ,which is assesed by a specialist or a user, related to the query

            $orders=array(); $assessments=array();
            foreach ($assessed_documents as $document=>$details)
            {
                $orders[]=$details[0];
                $assessments[]=$details[1];
            }
            asort($orders);

            foreach ($boundaries as $cutoff)
            {
                if(is_numeric($cutoff))
                {
                    if($total_result>=$cutoff)
                    {
                        $temporary=array('rank'=>array(),'pvalue'=>array());
                        $err[$cutoff][]=0;
                        $index=count($err[$cutoff])-1;

                        for($i=1;$i<=$cutoff;$i++)
                        {
                            if(in_array($i,$orders))
                            {
                                $temporary['rank'][]=1/$i;
                                $temporary['pvalue'][]=(pow(2,$assessments[array_search($i, $orders)])-1)/pow(2,$max_grade);
                            }
                        }

                        if(count($temporary['rank'])>0)
                        {
                            for($i=(count($temporary['rank'])-1);$i>=0;$i--)
                            {
                                $others=1;

                                if($i>0)
                                {
                                    for($j=0;$j<$i;$j++) { $others*=(1-$temporary['pvalue'][$j]); }
                                }

                                $err[$cutoff][$index]+=($temporary['rank'][$i]*$temporary['pvalue'][$i]*$others);
                            }
                        }
                    }
                }
                else
                {
                    $temporary=array('rank'=>array(),'value'=>array());
                    $err[$cutoff][]=0;
                    $index=count($err[$cutoff])-1;

                    for($i=1;$i<=$total_result;$i++)
                    {
                        if(in_array($i,$orders))
                        {
                            $temporary['rank'][]=1/$i;
                            $temporary['pvalue'][]=(pow(2,$assessments[array_search($i, $orders)])-1)/pow(2,$max_grade);
                        }
                    }

                    if(count($temporary['rank'])>0)
                    {
                        for($i=(count($temporary['rank'])-1);$i>=0;$i--)
                        {
                            $others=1;

                            if($i>0)
                            {
                                for($j=0;$j<$i;$j++) { $others*=(1-$temporary['pvalue'][$j]); }
                            }

                            $err[$cutoff][$index]+=($temporary['rank'][$i]*$temporary['pvalue'][$i]*$others);
                        }
                    }
                }
            }
        }

        //m_err calculation over all organised err values
        foreach ($boundaries as $cutoff)
        {
            $m_err[$cutoff]=array('count'=>0,'value'=>0);

            //is there any value for the related cutoff
            if(count($err[$cutoff])>0)
            {
                $m_err[$cutoff]['count']=count($err[$cutoff]);//how many err was used
                $m_err[$cutoff]['value']=array_sum($err[$cutoff])/count($err[$cutoff]);
            }
        }

        return $m_err;
    }

    public function bpref($interactions,$boundaries=array("all"))
    {
        $bpref=array();
        $m_bpref=array(); //mean bpref

        foreach ($interactions as $query)
        {
            $total_result=$query['total_result'];//list: total count of results
            $related_documents=array();

            $assessed_documents=$query['assessed_documents'];//the list of documents ,which is assesed by a specialist or a user, related to the query

            $orders=array(); $assessments=array();
            foreach ($assessed_documents as $document=>$details)
            {
                $orders[]=$details[0];
                $assessments[]=$details[1];

                if($details[1]==1) { $related_documents[]=$document; }
            }
            asort($orders);

            foreach ($boundaries as $cutoff)
            {
                if(is_numeric($cutoff))
                {
                    if($total_result>=$cutoff)
                    {
                        if(count($related_documents)>0)
                        {
                            $bpref[$cutoff][]=1/count($related_documents);
                            $index=count($bpref[$cutoff])-1;
                            $temp_calc=0;

                            foreach ($orders as $order_index => $order)
                            {
                                if($order<=$cutoff)
                                {
                                    $counter=0;
                                    if($assessments[$order_index]==1)
                                    {
                                        foreach ($orders as $order_index2 => $order2)
                                        {
                                            if($order2<$order)
                                            {
                                                if($assessments[$order_index2]==0) { $counter++; }
                                            }
                                            else { break; }
                                        }

                                        $temp_calc+=(1-($counter/count($related_documents)));
                                    }
                                }
                                else { break; }
                            }

                            $bpref[$cutoff][$index]*=$temp_calc;
                        }
                        else { $bpref[$cutoff][]=0; }
                    }
                }
                else
                {
                    if(count($related_documents)>0)
                    {
                        $bpref[$cutoff][]=1/count($related_documents);
                        $index=count($bpref[$cutoff])-1;
                        $temp_calc=0;

                        foreach ($orders as $order_index => $order)
                        {
                            $counter=0;
                            if($assessments[$order_index]==1)
                            {
                                foreach ($orders as $order_index2 => $order2)
                                {
                                    if($order2<$order)
                                    {
                                        if($assessments[$order_index2]==0) { $counter++; }
                                    }
                                    else { break; }
                                }

                                $temp_calc+=(1-($counter/count($related_documents)));
                            }
                        }

                        $bpref[$cutoff][$index]*=$temp_calc;
                    }
                    else { $bpref[$cutoff][]=0; }
                }
            }
        }

        //mean bpref calculation over all organised bpref values
        foreach ($boundaries as $cutoff)
        {
            $m_bpref[$cutoff]=array('count'=>0,'value'=>0);

            //is there any value for the related cutoff
            if(count($bpref[$cutoff])>0)
            {
                $m_bpref[$cutoff]['count']=count($bpref[$cutoff]);//how many bpref was used
                $m_bpref[$cutoff]['value']=array_sum($bpref[$cutoff])/count($bpref[$cutoff]);
            }
        }

        return $m_bpref;
    }
}