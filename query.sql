-- http://sqlfiddle.com/#!15/58532/1
CREATE TABLE tbl (id int not null, name text);

insert into tbl(id, name) values
(1, 'a'),
(1, 'a'),
(1, 'b'),
(2, 'c'),
(3, 'd');

select id
from tbl
group by id
having count(id) > 1;