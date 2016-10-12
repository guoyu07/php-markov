#Markov

This is a pretty naive PHP and SQL markov generator. Markov.php contains the functions to process text and feed it to the database, as well as generate
text based on what is in the database. In markov.php there are controls for the granularity of the text processing, as well as controls for text
generation. Included in this repo is a sample feed.txt (Alice in Wonderland chapter 1), as well as the SQL schema of the table required to store
the parsed text.