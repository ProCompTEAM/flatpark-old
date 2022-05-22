using DataCenter.Data.Base;
using System;
using System.Linq.Expressions;

namespace DataCenter.Data.Queries.Filters
{
    public abstract class BaseFilter<T> where T : class, IEntity
    {
        protected readonly QueryBuilder<T> builder = new QueryBuilder<T>();

        public Expression<Func<T, bool>> ToQuery()
        {
            return builder.Query;
        }
    }
}
