Load novel genome
======================

Here is a quick guide to describe how to load a novel genome into GenIE-Sys database. 

### Download
Let's assume we need to integrate Populus tremula v2.0 genome into GenIE-System. First, we need to download the required files. The latest version of the GFF3 and FASTA files are available on [PlantGenIE FTP](ftp://plantgenie.org/Data/PopGenIE/Populus_tremula/).

```shell
$ curl -O ftp://plantgenie.org/Data/PopGenIE/Populus_tremula/v2.2/gff/Potra02_genes.gff.gz
$ curl -O ftp://plantgenie.org/Data/PopGenIE/Populus_tremula/v2.2/fasta/Potra02_genome.fasta.gz
$ gzip -d Potra02_genes.gff.gz
$ gzip -d Potra02_genome.fasta.gz

$ awk '!/##/' Potra02_genes.gff  |head
chr1	maker	gene	8865	11259	.	-	.	ID=Potra2n1c1;Name=Potra2n1c1
chr1	maker	mRNA	8865	10802	.	-	.	ID=Potra2n1c1.3;Parent=Potra2n1c1;Name=Potra2n1c1.3;_AED=0.39;_eAED=0.37;_QI=192|0.66|0.75|1|0|0|4|0|115
chr1	maker	CDS	8865	9054	.	-	1	ID=Potra2n1c1.3:cds;Parent=Potra2n1c1.3
chr1	maker	CDS	9487	9559	.	-	2	ID=Potra2n1c1.3:cds;Parent=Potra2n1c1.3
chr1	maker	CDS	9669	9753	.	-	0	ID=Potra2n1c1.3:cds;Parent=Potra2n1c1.3
chr1	maker	exon	9669	9764	.	-	.	ID=Potra2n1c1.3:exon:300;Parent=Potra2n1c1.3
chr1	maker	five_prime_UTR	9754	9764	.	-	.	ID=Potra2n1c1.3:five_prime_utr;Parent=Potra2n1c1.3
chr1	maker	exon	10622	10802	.	-	.	ID=Potra2n1c1.3:exon:299;Parent=Potra2n1c1.3
chr1	maker	five_prime_UTR	10622	10802	.	-	.	ID=Potra2n1c1.3:five_prime_utr;Parent=Potra2n1c1.3
chr1	maker	mRNA	8865	11259	.	-	.	ID=Potra2n1c1.1;Parent=Potra2n1c1;Name=Potra2n1c1.1;_AED=0.22;_eAED=0.21;_QI=896|0.66|0.75|1|0|0|4|0|115

$ head Potra02_genome.fasta
>chr1
AGAGAGCTCTGTGGGTCATTACTGTCACAACTCCTAGCCAGCTTGAATAT
TCCATATAGCACATATCCTGGATGGGAAAGTTTGGTTAATGTGTGCTATT
CTTGCTCGCCTTCAACACGATTATTTCGTTCATACCACAAGAAATAAACA
GTAGTGGATAGTAGAAGGCGAGCTAGCATGTGATCACTGTTATTCTTCTT
CGTGTAGTGAGTGACTGACCAATGAAGCAATTGTGTCCACGGTTTGCATG
GCCAATAATGGTTGGCTCTGCGACAAATGGACTTCCAAACCAAGCTGGTG
TAACTGCATTCAAAAAAGAGGTGTTCATATGTTTCCATGTAAATTCCATA
TAGTATGCAAGTTGTATCTGTGACTCCTCCATGCAATCTATCCATCGTTC
TTAGTCTACCAAGGCTGGCTAACCAGAGTATAAATGAGTGACGAGGGATA
```

### Parse genome
Now we need to parse GFF3 and FASTA files into required formats. There are two primary tables(transcript_info and gene_info) in the database.
```shell
## generate file for gene_info table
awk '/gene/{split($9,a,"ID=");split(a[2],b,";");print b[1],$1,$4,$5,$7}' FS='\t' OFS='\t' Potra02_genes.gff > gene_info.txt
$ head gene_info.txt
Potra2n1c1	chr1	8865	11259	-
Potra2n1c2	chr1	21121	21603	+
Potra2n1c3	chr1	22295	24697	-
Potra2n1c4	chr1	30731	32811	+
Potra2n1c5	chr1	33508	33833	+
Potra2n1c6	chr1	50823	54726	-
Potra2n1c7	chr1	50901	51116	+
Potra2n1c8	chr1	54928	62450	-
Potra2n1c9	chr1	69471	73884	-
Potra2n1c10	chr1	74717	75583	+

## create file for transcript_info table
awk '$3~/gene/{g=$4" "$5}$3~/RNA$/{split($9,a,/[;=]/);for(i=1;i in a;i+=2)k[a[i]]=a[i+1]; print k["Name"], k["Parent"], "desc", $1, $7, g, "PAC", "PEP", $4,$5}' Potra02_genes.gff > transcript_info.txt
$ head transcript_info.txt
Potra2n1c1.3 Potra2n1c1 desc chr1 - 8865 11259 PAC PEP 8865 10802
Potra2n1c1.1 Potra2n1c1 desc chr1 - 8865 11259 PAC PEP 8865 11259
Potra2n1c1.2 Potra2n1c1 desc chr1 - 8865 11259 PAC PEP 8865 11259
Potra2n1c2.1 Potra2n1c2 desc chr1 + 21121 21603 PAC PEP 21121 21603
Potra2n1c3.1 Potra2n1c3 desc chr1 - 22295 24697 PAC PEP 22295 24697
Potra2n1c4.1 Potra2n1c4 desc chr1 + 30731 32811 PAC PEP 30731 32811
Potra2n1c5.1 Potra2n1c5 desc chr1 + 33508 33833 PAC PEP 33508 33833
Potra2n1c6.1 Potra2n1c6 desc chr1 - 50823 54726 PAC PEP 50823 54726
Potra2n1c7.1 Potra2n1c7 desc chr1 + 50901 51116 PAC PEP 50901 51116
Potra2n1c8.3 Potra2n1c8 desc chr1 - 54928 62450 PAC PEP 54928 61609
```

### Create database

Now we need to create a database. To do this, you need a MySQL username and password. If you use the MAMP installation default username and password would be `root`.
```shell
## Download all required scripts and dump database
$ git clone https://github.com/irusri/scripts.git
## Create database for default root user and root password
$ mysql -u root -proot
mysql> create database my_genie_sys_database;
Query OK, 1 row affected (0.01 sec)
mysql> use my_genie_sys_database;
Database changed
mysql> source scripts/dump.sql;
```

### Loading to primary tables

Now we need to load above two files(gene_info.txt and transcript_info.txt) into the newly created database. There is a script(`load_data.sh`) to do this. We can download the script and enter the correct `username, password and database` information to `DB_USER, DB_PASS and DB` parameters respectively.

```shell
$ nano scripts/load_data.sh
#load_data.sh script
#!/bin/bash
#load_data.sh
#USAGE: sh load_data.sh [table_name] [filename]
#sh load_data.sh transcript_info_x /tmp/transcript_info.tsv

DB_USER='root' #'your_db_username'
DB_PASS='root' #'your_password'
DB='my_genie_sys_database' #'database_name'

mysql  --host=localhost --user=$DB_USER --password=$DB_PASS --local_infile=1 --database=$DB <<EOFMYSQL
TRUNCATE TABLE $1;
ALTER TABLE $1 AUTO_INCREMENT = 1;
load data local infile '$2' ignore INTO TABLE $1 CHARACTER SET UTF8 fields terminated by '\t' LINES TERMINATED BY '\n' ignore 0 lines;
EOFMYSQL
```
Run following commands to load  `gene_info.txt` and `transcript_info.txt` into respective tables.
```shell
#Load above generated source file into gene_info table
.scripts/load_data.sh gene_info gene_info.txt

#Load previously generated source file into transcript_info table
.scripts/load_data.sh transcript_info transcript_info.txt

```
Now we need to update the `gene_i` parameter in `transcript_info` table. There is a script(`update_gene_i.sh`) in the scripts directory, we just need to enter the correct `username, password and database` information to `DB_USER, DB_PASS and DB` parameters respectively as we did in previous step.

```shell
$ nano scripts/update_gene_i.sh
#update_gene_i.sh script
#!/bin/bash
#update_gene_i.sh

DB_USER='root' #'your_db_username'
DB_PASS='root' #'your_password'
DB='my_genie_sys_database' #'database_name'

#USAGE: sh update_gene_i.sh

mysql --host=localhost --user=$DB_USER --password=$DB_PASS --local_infile=1 --database=$DB <<EOFMYSQL
create temporary table add_gene_i(gene_i MEDIUMINT NOT NULL AUTO_INCREMENT PRIMARY KEY, genename VARCHAR(40));
ALTER TABLE add_gene_i AUTO_INCREMENT = 1;
INSERT INTO add_gene_i(genename) select DISTINCT(gene_id) from transcript_info;
UPDATE transcript_info INNER join add_gene_i ON add_gene_i.genename = transcript_info.gene_id SET transcript_info.gene_i = add_gene_i.gene_i;
drop temporary table add_gene_i;
EOFMYSQL

```

Let's run the following command to update `gene_i` in `transcript_info` table.
```shell
#Finally update the gene_i in transcript_info table using update_gene_i.sh.
.scripts/update_gene_i.sh
```
