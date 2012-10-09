<?php
return array(
    'ParallelJobs\Module'       => __DIR__ . '/../Module.php',
    'ParallelJobs\System\Fork\ForkManager'       => __DIR__ . '/../src/ParallelJobs/System/Fork/ForkManager.php',
    'ParallelJobs\System\Fork\Exception\RuntimeException'=> __DIR__ . '/../src/ParallelJobs/System/Fork/Exception/RuntimeException.php',
    'ParallelJobs\System\Fork\Exception\ExceptionInterface'=> __DIR__ . '/../src/ParallelJobs/System/Fork/Exception/ExceptionInterface.php',
    'ParallelJobs\System\Fork\Storage\File'   => __DIR__ . '/../src/ParallelJobs/System/Fork/Storage/File.php',
    'ParallelJobs\System\Fork\Storage\Segment'   => __DIR__ . '/../src/ParallelJobs/System/Fork/Storage/Segment.php',
    'ParallelJobs\System\Fork\Storage\Memcached'   => __DIR__ . '/../src/ParallelJobs/System/Fork/Storage/Memcached.php',
    'ParallelJobs\System\Fork\Storage\StorageInterface'   => __DIR__ . '/../src/ParallelJobs/System/Fork/Storage/StorageInterface.php',
    'ParallelJobs\System\Fork\Storage\Results\Results'   => __DIR__ . '/../src/ParallelJobs/System/Fork/Storage/Results/Results.php',
    'ParallelJobs\System\Fork\Storage\Results\ResultsInterface'   => __DIR__ . '/../src/ParallelJobs/ParallelJobs/System/Fork/Storage/Results/ResultsInterface.php',
    'ParallelJobs\System\Fork\Storage\Result\Result'   => __DIR__ . '/../src/ParallelJobs/System/Fork/Storage/Result/Result.php',
    'ParallelJobs\System\Fork\Storage\Result\ResultInterface'   => __DIR__ . '/../src/ParallelJobs/System/Fork/Storage/Result/ResultInterface.php',
);