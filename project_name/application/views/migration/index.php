<!DOCTYPE html>
<html lang="en">
  <head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <meta name="description" content="">
    <meta name="author" content="">
    <link rel="icon" href="../../favicon.ico">

    <title>Easy Migration System for CodeIgniter</title>

    <link href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap.min.css" rel="stylesheet">

    <style>
        .starter-template {
          padding: 3rem 1.5rem;
          text-align: center;
        }
    </style>

  </head>

  <body>
    <div class="container">

    <h2 style='text-align:center'> Easy Migration System for CodeIgniter </h2>

      <div class="starter-template">

        <div class='row'>
            <?php echo $this->session->flashdata('message'); ?>
            <div class="col-sm-6">
            <?php
                echo form_open(base_url() . $this->router->class . "/");
            ?>
                <input type="text" name="filter" class="form-control">
                <br />
                <input type="submit" class="btn btn-success" value="Search" />
            <?php 
                echo form_close(); 
            ?>
            </div>
            <div class="col-sm-6">  
                <?php
                    echo form_open(base_url().$this->router->class."/run");
                ?>
                    <input type="number" placeholder="Number of migration" class="form-control" name="version">
                    <br />
                    <input type="submit" class="btn btn-warning" value="Run" />
                <?php echo form_close(); ?>
            </div>
        </div>
        <br />
        <br />
        <div class='row'>
            <div class="col-sm-6">
                <h4><i class="icon-th"></i> Currently <small> migration in your database control.</small></h4>
                <table class="table table-bordered">
                    <thead>
                        <tr>
                            <th>Your last migration:</th>
                            <th>Version</th>
                        </tr>
                    </thead>
                    <tbody>              
                    <?php foreach ($migrations as $migration): ?>
                        <tr>
                            <td><?php echo $migration->description; ?></td>
                            <td><?php echo $migration->version; ?></td>
                        </tr>
                    <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
            <div class="col-sm-6">
                <h4><i class="icon-th"></i> Files <small> available for migration.</small></h4>

                <table class="table table-bordered">
                    <thead>
                        <tr>
                            <th>File(s) in your migration folder:</th>
                        </tr>
                    </thead>
                    <tbody>  
                    <?php
                        for($a=2; $a < count($files)-1; $a++){
                        ?>
                            <tr>
                                <td><?php echo $files[$a]; ?></td>
                            </tr>
                        <?php
                        }
                    ?>
                    </tbody>
                </table>
            </div>
        </div>

      </div>

    </div>

    <script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/js/bootstrap.min.js"></script>
  </body>
</html>


