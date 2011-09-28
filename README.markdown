# Dibasic

Dibasic is a very flexible AJAX based MySQL data editor. It was intended to be used for people who would like to create a database driven website from scratch, i.e. without a CMS, but don't want to write the data editor themselves. In fact, it only contains the data editor, no tools for creating a webpage. It's very much like a framework to create forms as in FileMaker etc.

## Installation

Create an empty directory, which will hold all your Dibasic files and cd into it. I like to name this directory `admin/` and I will refer to this folder as the "admin folder" for the rest of this page.

	cd your-web-root
	mkdir admin
	cd !$

Then clone Dibasic into it:

	git clone https://github.com/lukasberns/Dibasic.git

Next edit the `Dibasic.config-sample.php` file inside the newly created Dibasic folder and rename it to `Dibasic.config.php`. Now visit the admin folder in your web browser. It should give you the instruction to setup the database. During setup, Dibasic will create the following files and directories inside your admin folder:

	index.php
	inputs/
	pages/
	plugins/
	uploaded/

Now you can log in using the username and password written on the page. The first thing to do after you logged into Dibasic is to change your username and password. The interface should make this an easy task.

## Creating a page

The next thing to do, is to create your custom page. You will create a page for every database table you want to edit. Dibasic can also create this table for you, if it doesn't exist yet.

### Declaring the table

Let's assume we're creating a blog. The very basic table that we need for that could look like this:

	TABLE posts
	(
		id INT AUTO_INCREMENT,
		title VARCHAR(255) NOT NULL,
		content TEXT NOT NULL,
		date_posted DATETIME NOT NULL
	)

During the setup process, Dibasic has created an empty directory named `pages/` inside your admin folder. Now create a file named `posts.php` and insert the following code into it:

	<?php
	
	$table = new Dibasic('posts');
	
	// ...

This tells Dibasic which table we will be editing. In this case, it's the `posts` table.

### Adding columns

Now we would like to declare the columns on the table, which we will do using the `addColumn()` method on `Dibasic`. The signature of it looks like this:

	Dibasic::addColumn($column_name, $input_type [, $title = '' [, $options = array() ]])

Where `$column_name` is the column name of the database table. `$input_type` specifies how the input interface should look like. If you look into `Dibasic/inputs/`, you will see many folders starting with `DI`. You can specify any of these while dropping the `DI` prefix. Popular input types are `Text` for a simple text field, `TextArea` for long text, `Date` for a date picker, `Checkbox` for a checkbox etc.

The `$title` argument will be displayed in the form as the label for the input.

Some input types require more options. For example the `Select` input type will create an HTML `<select>` tag and needs to know the options. To specify these, pass the following as the fourth argument to `addColumn`:

	$table->addColumn('fruit', 'Select', 'Fruit',
		array(
			'options' => array(
				'apple' => 'Apple',
				'banana' => 'Banana',
				'orange' => 'Orange'
			)
		)
	);

They key will be the value that will be stored in the database, while the value will be displayed to the user.

Coming back to our blog editor, let's add our columns:

	// ...
	
	$table->addColumn('title', 'Text', 'Title');
	$table->addColumn('content', 'TextArea', 'Content');
	$table->addColumn('date_posted', 'Timestamp', 'Date Posted', array( 'setOnUpdate' => false ));
	
	// ...

You don't need to specify the key column, i.e. the `id` column. Dibasic will take care of that. It can be named as you like, but it has to be a single auto incremented `INTEGER` column.

### Setting the DataRenderer

Next we need to tell Dibasic how we'd like to render existing data. We will use the `setDataRenderer()` method on `Dibasic`. The signature for it looks like this:

	Dibasic::setDataRenderer($renderer_name [, $options = array() ]);

`$renderer_name` is similar to the `$input_type` argument of the `addColumn` method. If you look into `Dibasic/plugins/`, you'll see many folders starting with `DP`. Some of them start with `DPData`. You can specify any name out of these that start with `DPData`, omitting the `DP` prefix (not `DPData`). In our case, we'd simply like to have a table showing all columns, so:

	// ...
	
	$renderer = $table->setDataRenderer('DataTable');
	
	// ...

If you want to selectively display some columns or change the order of them, you can call the method with the `columns` option, like this:

	$renderer = $table->setDataRenderer('DataTable', array( 'columns' => array('title', 'content') ));

To tell the DataRenderer how to order the items, call the `order()` method on `$renderer`:

	// ...
	
	$renderer->order('Date Posted', '-date_posted');
	
	// ...

The first argument is a description how you are ordering the items. The following options will be used as the ordering parameters. You can pass as many arguments as you like. If you only specify the column name, the data will be sorted in ascending order. If you prefix it with a minus sign, it will be sorted in descending order.

By calling the `order()` method multiple times, the user is given a `<select>` box where he can choose how he would like to order the data, out of the options you have given.

### Finishing the file

Finally, we need to call the `run()` method on `Dibasic`:

	// ...
	
	$table->run();
	
	?>

So the complete file will look like this:

	<?php
	
	$table = new Dibasic('posts');
	
	$table->addColumn('title', 'Text', 'Title');
	$table->addColumn('content', 'TextArea', 'Content');
	$table->addColumn('date_posted', 'Timestamp', 'Date Posted', array( 'setOnUpdate' => false ));
	
	$renderer = $table->setDataRenderer('DataTable');
	$renderer->order('Date Posted', '-date_posted');
	
	$table->run();
	
	?>

That was easy, wasn't it?

### Adding the page in the database

For the newly created page to become usable, you need to tell Dibasic about it. Log into Dibasic and go to `Dibasic » Pages`. Click on `Add` and fill in the data like this:

	Title: Posts
	Group name: 
	Filename: posts.php
	Filename for users without permission: 
	Users can open by default: [check]
	Insert at: [Beginning]

Now you should see that a new item named `Posts` has appeared in the navigation bar. Click on it. Voila, that's your posts editor. (If the `posts` table didn't exist before, Dibasic will show you a `Create` button to create the table for you.)
