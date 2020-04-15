# IREEL: Information Retrieval (IR) Effectiveness Evaluation Library for PHP

This library was created in order to evaluate any kind of algorithm used in IR systems and analyze how well they perform. For this purpose,
14 different effectiveness measurements have been put together. All of these measurements consist of mostly used ones in the literature. They are as follow:

* Average Precision @n (AP@n)
* Mean Average Precision (MAP)
* Geometric Mean Average Precision (GMAP)
* Eleven Point - Interpolated Average Precision (IAP)
* R-Precision
* F-Measure
* Cumulative Gain (CG)
* Normalized Cumulative Gain (NCG)
* Discounted Cumulative Gain (DCG)
* Normalized Discounted Cumulative Gain (NDCG)
* Mean Reciprocal Rank (MRR)
* Rank-Biased Precision (RBP)
* Expected Reciprocal Rank (ERR)
* BPref

This library has also 5 datasets, which were organized for learning and testing each method with their different parameters.
Even though this library was dynamically used on an online IR system with real user data, it can be used for static datasets as well.

## Before Starting:
### Some explanations about datasets
Shared datasets are in the format of txt. Each row in the datasets was separated by a pipeline. Even though these datasets have similar attributes, they were created in different formats and have been used in different measurements together or separately for showing how the methods work and what kind of attributes(parameters) these methods need.
The attributes used in the datasets are as follow: 

* **id**: this is just the id of an interaction
* **query_id**: this could be thought as a session id
* **total_result**: how many results are returned to a query
* **related_document_id**: this is the id of a document which is already known as related to a query. For better understanding, it might be thought that as if a specialist has determined the related documents corresponding to a query before
* **visited_or_related_document_id**: when thinking of an online system, the id of a clicked page on the result list could be assumed as a related document id corresponding to a query. On the other hand, this case could also be counted as the same for static datasets. For example, let's say an algorithm is tested and a query was submitted. The id’s of some documents in the result list according to related_document_id attribute could be assumed as a related document id
* **order_number_of_the_document**: the order number of the clicked/related document in the result list
* **assessment_of_the_document**: the assessment, which belongs to the clicked/related document. This attribute is in the range of numeric values for this library (1-5) and has to be the same format for different studies, even if the range limits change (for example: 0-5, 1-3, 0-3, 1-7, eg.)
* **judgement_of_the_document**: the judgement, which belongs to the clicked/related document. This attribute has boolean values 0 or 1 which indicate whether a page/document is related to a query or not

### The parameters used in the methods
There are 5 different parameters used in the methods. While some of them are used in common, some of them are used separately. All parameters and their explanations are as follow:
* **data**: this parameter consists of assessments (as a range) of a user or a specialist, judgements (as boolean) of a user or a specialist and user interactions and is used in every method.
* **boundaries (Default: ['all'])**: this parameter means cut-off points on total result. In this way, we can analyze performance based on cut-off points. Numeric values such as 5, 10, eg. or string values such as 'all' can be assigned to it in an array format. If a cut-off point is numeric and lower than total result, the processed row is going to be added to the total calculation. On the other hand, in a case that a cut-off point is not numeric, all the rows in the datasets are used in the calculation.
* **constant (Default: '0.3')** : this parameter is just used in the calculation of GMAP in order to avoid zero values during the calculation.
* **persistence (or probability) levels (Default: [0.5, 0.8, 0.95])**: this parameter is just used in the calculation of RBP.
* **max_grade_level (Default: '5')**: this parameter is just used in the calculation of ERR parameter. The minimum level is also used in the calculation but does not need to be included as a parameter for this measurement.

## Installing

The package can be installed using the code below:

```
composer require ozcan39/ir_evaluation_php
```

## Importing to a study
After installing the package, the code below is used:
```
require_once ('vendor/autoload.php');
use \ir_evaluation\effectiveness;

$ir=new effectiveness(); # --> an object, which we can use all methods in it, is created
```

The example datasets might not be preferred to include in case of that different dataset will be used.

## Viewing the datasets
The example below is for viewing the dataset 1.

```
$dataset = fopen("vendor/ozcan39/ir_evaluation_php/src/datasets/dataset1.txt", "r") or die("Unable to open file!");
while(!feof($dataset))
{
    var_dump(trim(fgets($dataset)));
}
```

The other datasets can be viewed by just changing the number between 1 and 5 at the end of the term **dataset1** on the first line.

## Usage of the methods
As mentioned before, each method uses some of the datasets together or separately. In this direction, the methods, which need the same datasets, have been explained together. 

#### Precision based methods: AP@n, MAP, GMAP, IAP, R-Precision and F-Measure 
These methods use dataset3 and dataset4 together. Before calling the methods, a variable named **`interactions`** is created as follow:

```
$interactions=array();

# dataset3 formation: id|query_id|related_document_id

$dataset = fopen("vendor/ozcan39/ir_evaluation_php/src/datasets/dataset3.txt", "r") or die("Unable to open file!");
while(!feof($dataset))
{
    $row=explode('|',trim(fgets($dataset)));
    $interactions[$row[1]]['related_documents'][]=$row[2];
}
fclose($dataset);

# dataset4 formation: id|query_id|total_result|visited_or_related_document_id|order_number_of_the_document

$dataset2 = fopen("vendor/ozcan39/ir_evaluation_php/src/datasets/dataset4.txt", "r") or die("Unable to open file!");
while(!feof($dataset2))
{
    $row=explode('|',trim(fgets($dataset2)));
    $interactions[$row[1]]['total_result']=$row[2];
    $interactions[$row[1]]['visited_documents'][]=$row[3];
    $interactions[$row[1]]['visited_documents_orders'][$row[3]]=$row[4];
}
fclose($dataset2);
```

After creating interactions variable, the usage of the related methods is below:

```
########################################################################################
# parameters => (data, boundaries)

echo "<h1>Average Precision@n</h1>";
$ap_at_n=$ir->ap_at_n($interactions,array(5,10,15,20,'all'));
var_dump($ap_at_n);

echo "<h1>R-Precision</h1>";
$rprecision=$ir->rprecision($interactions,array(5,10,15,20,'all'));
var_dump($rprecision);

echo "<h1>Mean Average Precision</h1>";
$mean_ap=$ir->mean_ap($interactions,array(5,10,15,20,'all'));
var_dump($mean_ap);

echo "<h1>F-Measure</h1>";
$fmeasure=$ir->fmeasure($interactions,array(5,10,15,20,'all'));
var_dump($fmeasure);
########################################################################################
# parameters -> (data, constant, boundaries)

echo "<h1>Geometric Mean Average Precision</h1>";
$gmap=$ir->gmap($interactions,0.3,array(5,10,15,20,'all'));
var_dump($gmap);
########################################################################################
# parameters -> (data)

echo "<h1>Eleven Point - Interpolated Average Precision</h1>";
echo "<h4>Recall => Precision</h4>";
$iap=$ir->iap($interactions);
var_dump($iap);
```
#### Gain based methods: CG, NCG, DCG and NDCG
These methods use just dataset1. Before calling the methods, a variable named interactions is created as follow:

```
$interactions=array();

# dataset1 formation: id|query_id|total_result|visited_or_related_document_id|order_number_of_the_document|assessment_of_the_document
# assessment_of_the_document: assessment is between 1 and 5 for this example

$dataset = fopen("vendor/ozcan39/ir_evaluation_php/src/datasets/dataset1.txt", "r") or die("Unable to open file!");
while(!feof($dataset))
{
    $row=explode('|',trim(fgets($dataset)));
    $interactions[$row[1]]['total_result']=$row[2];
    $interactions[$row[1]]['assessed_documents'][$row[3]]=array($row[4],$row[5]);
}
fclose($dataset);
```

After creating interactions variable, the usage of the related methods is below:

```
# parameters => (data, boundaries)

echo "<h1>Cumulative Gain</h1>";
$cgain=$ir->cgain($interactions,array(5,10,15,20,"all"));
var_dump($cgain);

echo "<h1>Normalized Cumulative Gain</h1>";
$ncgain=$ir->ncgain($interactions,array(5,10,15,20));
var_dump($ncgain);

echo "<h1>Discounted Cumulative Gain</h1>";
$dcgain=$ir->dcgain($interactions,array(5,10,15,20));
var_dump($dcgain);

echo "<h1>Normalized Discounted Cumulative Gain</h1>";
$ndcgain=$ir->ndcgain($interactions,array(5,10,15,20,"all"));
var_dump($ndcgain);
```

#### Mean Reciprocal Rank (MRR) 
This method use just dataset2. Before calling the method, a variable named interactions is created as follow:

```
$interactions=array();

# dataset2 formation: id|query_id|visited_or_related_document_id|order_number_of_the_document

$dataset = fopen("vendor/ozcan39/ir_evaluation_php/src/datasets/dataset2.txt", "r") or die("Unable to open file!");
while(!feof($dataset))
{
    $row=explode('|',trim(fgets($dataset)));
    $interactions[$row[1]]['visited_documents_orders'][]=$row[3];
}
fclose($dataset);
```

After creating interactions variable, the usage of the method is below:

```
# parameters => (data)

echo "<h1>Mean Reciprocal Rank</h1>";
$mrr=$ir->mrr($interactions);
var_dump($mrr);
```

#### Rank-Biased Precision (RBP)
This method use just dataset4. Before calling the method, a variable named interactions is created as follow:

```
$interactions=array();

# dataset4 formation: id|query_id|total_result|visited_or_related_document_id|order_number_of_the_document

$dataset = fopen("vendor/ozcan39/ir_evaluation_php/src/datasets/dataset4.txt", "r") or die("Unable to open file!");
while(!feof($dataset))
{
    $row=explode('|',trim(fgets($dataset)));
    $interactions[$row[1]]['total_result']=$row[2];
    $interactions[$row[1]]['visited_documents'][]=$row[3];
    $interactions[$row[1]]['visited_documents_orders'][$row[3]]=$row[4];
}
fclose($dataset);
```

After creating interactions variable, the usage of the method is below:

```
# parameters => (data, persistence (or probability) levels, boundaries)

echo "<h1>Rank Biased Precision</h1>";
$rbprecision=$ir->rbprecision($interactions,array(0.5,0.8,0.95),array(5,10,15,20,'all'));
var_dump($rbprecision);
```

#### Expected Reciprocal Rank (ERR)
This method use just dataset1. Before calling the method, a variable named interactions is created as follow:

```
$interactions=array();

# dataset1 formation: id|query_id|total_result|visited_or_related_document_id|order_number_of_the_document|assessment_of_the_document
# assessment_of_the_document: assessment is between 1 and 5 for this example

$dataset = fopen("vendor/ozcan39/ir_evaluation_php/src/datasets/dataset1.txt", "r") or die("Unable to open file!");
while(!feof($dataset))
{
    $row=explode('|',trim(fgets($dataset)));
    $interactions[$row[1]]['total_result']=$row[2];
    $interactions[$row[1]]['assessed_documents'][$row[3]]=array($row[4],$row[5]);
}
fclose($dataset);
```

After creating interactions variable, the usage of the method is below:

```
# parameters => (data, max_grade_level, boundaries)

echo "<h1>Expected Reciprocal Rank</h1>";
$err=$ir->err($interactions,5,array(5,10,15,20,"all"));
var_dump($err);
```

#### BPref
This method use just dataset5. Before calling the method, a variable named interactions is created as follow:

```
$interactions=array();

# dataset5 just consists of judged documents. Similar to dataset1, but last column has 2 different (boolean) values: 1: related, 0: unrelated
# data, which belong to unjudged documents, do not need to be inside of the dataset
# dataset5 formation: id|query_id|total_result|visited_or_related_document_id|order_number_of_the_document|judgement_of_the_document

$dataset = fopen("vendor/ozcan39/ir_evaluation_php/src/datasets/dataset5.txt", "r") or die("Unable to open file!");
while(!feof($dataset))
{
    $row=explode('|',trim(fgets($dataset)));
    $interactions[$row[1]]['total_result']=$row[2];
    $interactions[$row[1]]['assessed_documents'][$row[3]]=array($row[4],$row[5]);
}
fclose($dataset);
```

After creating interactions variable, the usage of the method is below:

```
# parameters => (data, boundaries)

echo "<h1>BPREF</h1>";
$bpref=$ir->bpref($interactions,array(5,10,15,20,"all"));
var_dump($bpref);
```

## How the analysis result is shown
If the method has boundaries parameter, the results are shown for every cut-off point separately. For example:

```
Mean Average Precision:
{
    5: {'count': 27, 'value': 0.33497942386831275},
    10: {'count': 19, 'value': 0.3966374269005848},
    15: {'count': 9, 'value': 0.4420940170940171},
    20: {'count': 3, 'value': 0.6923076923076922},
    'all': {'count': 28, 'value': 0.3850509113901969}
}

for MAP@5: 27 row records were used in total for calculation and the result has been found as the value (0.33497942386831275).
In this calculation, if the query has a result equal to 5 or higher than 5, the processed row data is added to the calculation process.
```

If the method has boundaries and persistence (or probability) levels parameters, the results are shown for every cut-off point and persistence (or probability) levels separately. For example:

```
Rank Biased Precision:
{
    5: {
        '0.5': {'count': 27, 'value': 0.1724537037037037},
        '0.8': {'count': 27, 'value': 0.10601481481481481},
        '0.95': {'count': 27, 'value': 0.0339625578703704}
        },
    10: {
        '0.5': {'count': 19, 'value': 0.18045847039473684},
        '0.8': {'count': 19, 'value': 0.11351753566315788},
        '0.95': {'count': 19, 'value': 0.042275296240743256}
        },
    15: {
        '0.5': {'count': 9, 'value': 0.2048882378472222},
        '0.8': {'count': 9, 'value': 0.12131197674382221},
        '0.95': {'count': 9, 'value': 0.042236674585140445}
        },
    20: {
        '0.5': {'count': 3, 'value': 0.3333740234375},
        '0.8': {'count': 3, 'value': 0.13791463178239996},
        '0.95': {'count': 3, 'value': 0.04233933479437731}
        },
    'all': {
        '0.5': {'count': 28, 'value': 0.17031478881835938},
        '0.8': {'count': 28, 'value': 0.1224766315254345},
        '0.95': {'count': 28, 'value': 0.04952452518068968}
        }
}
```

If the method has just data parameter, the results are shown as a single value except for IAP method. For example:

```
Mean Reciprocal Rank:
0.3965163308913308

##############################################################

Eleven Point - Interpolated Average Precision:
Recall => Precision
{
    '0.0': 0.40527483429269134,
    '0.1': 0.40527483429269134,
    '0.2': 0.40527483429269134,
    '0.3': 0.40527483429269134,
    '0.4': 0.40527483429269134,
    '0.5': 0.40527483429269134,
    '0.6': 0.3731319771498342,
    '0.7': 0.3731319771498342,
    '0.8': 0.3731319771498342,
    '0.9': 0.3731319771498342,
    '1.0': 0.3731319771498342
}
```

## License
This library is distributed under the [LGPL 2.1 license](https://www.gnu.org/licenses/old-licenses/lgpl-2.1.html). Please read LICENSE for information on the library availability and distribution.

## For citation this library
This section is going to be updated.

## For further reading about the measurements
**Average Precision @n, Mean Average Precision (MAP), R-Precision**: 

Ricardo Baeza-Yates and Berthier Ribeiro-Neto. 2011. Modern Information Retrieval: The concepts and technology behind search (2nd. ed.). Addison-Wesley Publishing Company, USA.

**Geometric Mean Average Precision:**
https://trec.nist.gov/pubs/trec15/appendices/CE.MEASURES06.pdf

**Eleven Point - Interpolated Average Precision (IAP):** 

Bruce Croft, Donald Metzler, and Trevor Strohman. 2009. Search Engines: Information Retrieval in Practice (1st. ed.). Addison-Wesley Publishing Company, USA.

**F-Measure:** 

C. J. Van Rijsbergen. 1979. Information Retrieval (2nd. ed.). Butterworth-Heinemann, USA.

**Cumulative Gain, Normalized Cumulative Gain, Discounted Cumulative Gain, Normalized Discounted Cumulative Gain:**
 
Kalervo Järvelin and Jaana Kekäläinen. 2000. IR evaluation methods for retrieving highly relevant documents. In Proceedings of the 23rd annual international ACM SIGIR conference on Research and development in information retrieval (SIGIR ’00). Association for Computing Machinery, New York, NY, USA, 41–48. DOI:https://doi.org/10.1145/345508.345545

Kalervo Järvelin and Jaana Kekäläinen. 2002. Cumulated gain-based evaluation of IR techniques. ACM Trans. Inf. Syst. 20, 4 (October 2002), 422–446. DOI:https://doi.org/10.1145/582415.582418

**Mean Reciprocal Rank:**
 
Ellen Voorhees. 1999. The TREC-8 Question Answering Track Report. Proceedings of the 8th Text Retrieval Conference. 77-82.

**Rank-Biased Precision (RBP):**
 
Alistair Moffat and Justin Zobel. 2008. Rank-biased precision for measurement of retrieval effectiveness. ACM Trans. Inf. Syst. 27, 1, Article 2 (December 2008), 27 pages. DOI:https://doi.org/10.1145/1416950.1416952

**Expected Reciprocal Rank:**
 
Olivier Chapelle, Donald Metlzer, Ya Zhang, and Pierre Grinspan. 2009. Expected reciprocal rank for graded relevance. In Proceedings of the 18th ACM conference on Information and knowledge management (CIKM ’09). Association for Computing Machinery, New York, NY, USA, 621–630. DOI:https://doi.org/10.1145/1645953.1646033

**Bpref:**
 
Chris Buckley and Ellen M. Voorhees. 2004. Retrieval evaluation with incomplete information. In Proceedings of the 27th annual international ACM SIGIR conference on Research and development in information retrieval (SIGIR ’04). Association for Computing Machinery, New York, NY, USA, 25–32. DOI:https://doi.org/10.1145/1008992.1009000


